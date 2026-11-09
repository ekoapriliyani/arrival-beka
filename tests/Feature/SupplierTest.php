<?php

namespace Tests\Feature;

use App\Models\ArrivalHeader;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The supplier payload posted by the management form.
     *
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'PT Sinar Baja',
            'code' => 'SB',
            'delimiter' => '|',
            'barcode_format' => 'manufacturer,grade,heat_no,coil_no,weight,size,qr_date',
        ], $overrides);
    }

    /**
     * The management page lists every saved supplier.
     */
    public function test_index_displays_the_supplier_management_page(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'PT Sinar Baja']);

        $response = $this->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertViewIs('suppliers');
        $response->assertSee('Kelola Supplier');
        $response->assertSee('PT Sinar Baja');
        $response->assertSee($supplier->barcode_format);
    }

    /**
     * A valid supplier is persisted with its own barcode layout.
     */
    public function test_store_creates_a_supplier(): void
    {
        $response = $this->postJson(route('suppliers.store'), $this->payload());

        $response->assertCreated();
        $response->assertJsonPath('name', 'PT Sinar Baja');
        $response->assertJsonPath('delimiter', '|');
        $response->assertJsonPath('arrivals_count', 0);
        $this->assertDatabaseHas('suppliers', [
            'name' => 'PT Sinar Baja',
            'delimiter' => '|',
        ]);
    }

    /**
     * Name, delimiter and barcode format are mandatory.
     */
    public function test_store_requires_the_core_supplier_fields(): void
    {
        $response = $this->postJson(route('suppliers.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'delimiter', 'barcode_format']);
    }

    /**
     * Two suppliers cannot share the same name.
     */
    public function test_store_rejects_a_duplicated_name(): void
    {
        Supplier::factory()->create(['name' => 'PT Sinar Baja']);

        $response = $this->postJson(route('suppliers.store'), $this->payload());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * A barcode format carrying an unknown field is refused.
     */
    public function test_store_rejects_an_unknown_barcode_field(): void
    {
        $response = $this->postJson(route('suppliers.store'), $this->payload([
            'barcode_format' => 'manufacturer,grade,heat_no,coil_no,weight,size,batch_no',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['barcode_format']);
    }

    /**
     * A barcode format missing a field required by an arrival is refused.
     */
    public function test_store_rejects_a_format_missing_required_fields(): void
    {
        $response = $this->postJson(route('suppliers.store'), $this->payload([
            'barcode_format' => 'manufacturer,grade,heat_no,coil_no,weight',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['barcode_format']);
    }

    /**
     * A barcode format repeating a field is refused.
     */
    public function test_store_rejects_a_duplicated_barcode_field(): void
    {
        $response = $this->postJson(route('suppliers.store'), $this->payload([
            'barcode_format' => 'manufacturer,grade,heat_no,coil_no,weight,size,heat_no',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['barcode_format']);
    }

    /**
     * The barcode format and delimiter are stored in a normalised shape.
     */
    public function test_store_normalises_the_delimiter_and_format(): void
    {
        $response = $this->postJson(route('suppliers.store'), $this->payload([
            'delimiter' => '  |  ',
            'barcode_format' => ' manufacturer ,  grade ,heat_no,coil_no,weight,size,qr_date ',
        ]));

        $response->assertCreated();
        $response->assertJsonPath('delimiter', '|');
        $response->assertJsonPath('barcode_format', 'manufacturer,grade,heat_no,coil_no,weight,size,qr_date');
    }

    /**
     * An update changes how that supplier's barcodes are parsed.
     */
    public function test_update_changes_the_delimiter_and_format(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->putJson(route('suppliers.update', $supplier), $this->payload([
            'name' => 'PT Sinar Baja',
            'delimiter' => ';',
            'barcode_format' => 'heat_no,coil_no,manufacturer,grade,weight,size,qr_date',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'delimiter' => ';',
            'barcode_format' => 'heat_no,coil_no,manufacturer,grade,weight,size,qr_date',
        ]);
    }

    /**
     * A supplier nothing points at can be removed.
     */
    public function test_destroy_removes_an_unused_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->deleteJson(route('suppliers.destroy', $supplier));

        $response->assertOk();
        $this->assertDatabaseCount('suppliers', 0);
    }

    /**
     * A supplier still booked on an arrival cannot be removed.
     */
    public function test_destroy_is_refused_while_arrivals_use_the_supplier(): void
    {
        $supplier = Supplier::factory()->create();
        ArrivalHeader::factory()->create(['supplier_id' => $supplier->id]);

        $response = $this->deleteJson(route('suppliers.destroy', $supplier));

        $response->assertUnprocessable();
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseCount('suppliers', 1);
        $this->assertDatabaseCount('arrival_headers', 1);
    }
}
