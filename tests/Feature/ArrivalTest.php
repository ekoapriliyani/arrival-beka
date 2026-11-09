<?php

namespace Tests\Feature;

use App\Models\ArrivalDetail;
use App\Models\ArrivalHeader;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArrivalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The scanner page lists saved headers, newest first.
     */
    public function test_index_returns_headers_newest_first(): void
    {
        $older = ArrivalHeader::factory()->create();
        $newer = ArrivalHeader::factory()->create();

        $response = $this->getJson(route('arrivals.index'));

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonPath('0.id', $newer->id);
        $response->assertJsonPath('1.id', $older->id);
    }

    /**
     * The history list shows how many scans each header holds.
     */
    public function test_index_includes_the_scan_count(): void
    {
        $header = ArrivalHeader::factory()->create();
        ArrivalDetail::factory()->count(2)->create(['arrival_header_id' => $header->id]);

        $response = $this->getJson(route('arrivals.index'));

        $response->assertOk();
        $response->assertJsonPath('0.details_count', 2);
    }

    /**
     * A valid header form is persisted as a header row.
     */
    public function test_store_creates_a_header(): void
    {
        $supplier = Supplier::factory()->create();

        $payload = [
            'po_number' => 'PO-12345',
            'tanggal' => '2026-11-07',
            'item' => 'Wire Rod',
            'description' => 'Pengiriman batch pertama',
            'supplier_id' => $supplier->id,
        ];

        $response = $this->postJson(route('arrivals.store'), $payload);

        $response->assertCreated();
        $response->assertJson($payload);
        $response->assertJsonPath('supplier.name', $supplier->name);
        $this->assertDatabaseHas('arrival_headers', $payload);
    }

    /**
     * Description is optional, the other header fields are not.
     */
    public function test_store_requires_the_core_header_fields(): void
    {
        $response = $this->postJson(route('arrivals.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['po_number', 'tanggal', 'item', 'supplier_id']);
    }

    /**
     * A header cannot be booked against an unknown supplier.
     */
    public function test_store_rejects_an_unknown_supplier(): void
    {
        $response = $this->postJson(route('arrivals.store'), [
            'po_number' => 'PO-777',
            'tanggal' => '2026-11-07',
            'item' => 'Wire Rod',
            'supplier_id' => 999999,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['supplier_id']);
    }

    /**
     * An empty description is stored as null.
     */
    public function test_store_accepts_a_missing_description(): void
    {
        $response = $this->postJson(route('arrivals.store'), [
            'po_number' => 'PO-99',
            'tanggal' => '2026-11-07',
            'item' => 'Wire Rod',
            'supplier_id' => Supplier::factory()->create()->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('arrival_headers', ['description' => null]);
    }

    /**
     * Opening a PO returns its header with scans in scan order.
     */
    public function test_show_returns_the_header_with_its_scans(): void
    {
        $header = ArrivalHeader::factory()->create();
        $first = ArrivalDetail::factory()->create(['arrival_header_id' => $header->id]);
        $second = ArrivalDetail::factory()->create(['arrival_header_id' => $header->id]);

        $response = $this->getJson(route('arrivals.show', $header));

        $response->assertOk();
        $response->assertJsonPath('id', $header->id);
        $response->assertJsonPath('details.0.id', $first->id);
        $response->assertJsonPath('details.1.id', $second->id);
    }

    /**
     * The header carries its supplier so the scanner knows how to parse barcodes.
     */
    public function test_show_returns_the_supplier_with_its_barcode_settings(): void
    {
        $supplier = Supplier::factory()->create([
            'delimiter' => '|',
            'barcode_format' => 'heat_no,coil_no,manufacturer,grade,weight,size,qr_date',
        ]);
        $header = ArrivalHeader::factory()->create(['supplier_id' => $supplier->id]);

        $response = $this->getJson(route('arrivals.show', $header));

        $response->assertOk();
        $response->assertJsonPath('supplier.id', $supplier->id);
        $response->assertJsonPath('supplier.delimiter', '|');
        $response->assertJsonPath('supplier.barcode_format', 'heat_no,coil_no,manufacturer,grade,weight,size,qr_date');
    }

    /**
     * An unknown header cannot be opened.
     */
    public function test_show_returns_not_found_for_an_unknown_header(): void
    {
        $response = $this->getJson(route('arrivals.show', 999));

        $response->assertNotFound();
    }

    /**
     * Deleting a header removes its scans as well.
     */
    public function test_destroy_removes_the_header_and_its_scans(): void
    {
        $header = ArrivalHeader::factory()->create();
        ArrivalDetail::factory()->count(3)->create(['arrival_header_id' => $header->id]);

        $response = $this->deleteJson(route('arrivals.destroy', $header));

        $response->assertOk();
        $this->assertDatabaseCount('arrival_headers', 0);
        $this->assertDatabaseCount('arrival_details', 0);
    }
}
