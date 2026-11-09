<?php

namespace Tests\Feature;

use App\Models\ArrivalDetail;
use App\Models\ArrivalHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A scanned QR tag is stored under the active header.
     */
    public function test_store_saves_the_scan_under_the_header(): void
    {
        $header = ArrivalHeader::factory()->create();

        $response = $this->postJson(route('arrivals.details.store', $header), [
            'manufacturer' => 'Nippon Steel',
            'grade' => 'SWRH82B',
            'heat_no' => '12345678',
            'coil_no' => '87654321',
            'weight' => 2.369,
            'size' => 5.5,
            'qr_date' => '2026-11-01',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('arrival_header_id', $header->id);
        $this->assertDatabaseHas('arrival_details', [
            'arrival_header_id' => $header->id,
            'heat_no' => '12345678',
            'coil_no' => '87654321',
        ]);
        $this->assertNotNull(ArrivalDetail::firstOrFail()->scanned_at);
    }

    /**
     * A scan without the core QR fields is rejected.
     */
    public function test_store_requires_the_core_scan_fields(): void
    {
        $header = ArrivalHeader::factory()->create();

        $response = $this->postJson(route('arrivals.details.store', $header), ['weight' => 1]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'manufacturer',
            'grade',
            'heat_no',
            'coil_no',
            'size',
        ]);
    }

    /**
     * A QR date that is not a real date is rejected.
     */
    public function test_store_rejects_an_invalid_qr_date(): void
    {
        $header = ArrivalHeader::factory()->create();

        $response = $this->postJson(route('arrivals.details.store', $header), [
            'manufacturer' => 'Nippon Steel',
            'grade' => 'SWRH82B',
            'heat_no' => '12345678',
            'coil_no' => '87654321',
            'weight' => 2.369,
            'size' => 5.5,
            'qr_date' => 'bukan-tanggal',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['qr_date']);
    }

    /**
     * A non numeric weight or size is rejected.
     */
    public function test_store_rejects_a_non_numeric_weight(): void
    {
        $header = ArrivalHeader::factory()->create();

        $response = $this->postJson(route('arrivals.details.store', $header), [
            'manufacturer' => 'Nippon Steel',
            'grade' => 'SWRH82B',
            'heat_no' => '12345678',
            'coil_no' => '87654321',
            'weight' => 'banyak',
            'size' => 5.5,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['weight']);
    }

    /**
     * Clearing one PO leaves the header and other POs untouched.
     */
    public function test_clear_details_only_empties_the_given_header(): void
    {
        $header = ArrivalHeader::factory()->create();
        $other = ArrivalHeader::factory()->create();
        ArrivalDetail::factory()->count(2)->create(['arrival_header_id' => $header->id]);
        ArrivalDetail::factory()->count(3)->create(['arrival_header_id' => $other->id]);

        $response = $this->deleteJson(route('arrivals.details.clear', $header));

        $response->assertOk();
        $response->assertJson(['deleted' => 2]);
        $this->assertDatabaseCount('arrival_details', 3);
        $this->assertDatabaseHas('arrival_headers', ['id' => $header->id]);
    }

    /**
     * Scans cannot be posted against an unknown header.
     */
    public function test_store_returns_not_found_for_an_unknown_header(): void
    {
        $response = $this->postJson(route('arrivals.details.store', 999), [
            'manufacturer' => 'Nippon Steel',
            'grade' => 'SWRH82B',
            'heat_no' => '12345678',
            'coil_no' => '87654321',
            'weight' => 2.369,
            'size' => 5.5,
        ]);

        $response->assertNotFound();
    }
}
