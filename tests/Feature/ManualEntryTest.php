<?php

namespace Tests\Feature;

use App\Models\ArrivalDetail;
use App\Models\ArrivalHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualEntryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The detail payload posted by the manual fallback form.
     *
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'manufacturer' => 'Nippon Steel',
            'grade' => 'SWRH82B',
            'heat_no' => '12345678',
            'coil_no' => '87654321',
            'weight' => 2.369,
            'size' => 5.5,
            'qr_date' => '2026-11-01',
            'source' => 'manual',
        ], $overrides);
    }

    /**
     * A manual entry is stored and flagged as manual.
     */
    public function test_store_saves_a_manual_entry_with_the_manual_source(): void
    {
        $header = ArrivalHeader::factory()->create();

        $response = $this->postJson(route('arrivals.details.store', $header), $this->payload());

        $response->assertCreated();
        $response->assertJsonPath('source', 'manual');
        $this->assertDatabaseHas('arrival_details', [
            'arrival_header_id' => $header->id,
            'heat_no' => '12345678',
            'source' => 'manual',
        ]);
    }

    /**
     * An entry posted without a source is treated as a normal scan.
     */
    public function test_store_defaults_to_scan_when_no_source_is_given(): void
    {
        $header = ArrivalHeader::factory()->create();

        $response = $this->postJson(
            route('arrivals.details.store', $header),
            $this->payload(['source' => null])
        );

        $response->assertCreated();
        $response->assertJsonPath('source', 'scan');
    }

    /**
     * An unknown source is rejected instead of being stored silently.
     */
    public function test_store_rejects_an_unknown_source(): void
    {
        $header = ArrivalHeader::factory()->create();

        $response = $this->postJson(route('arrivals.details.store', $header), $this->payload([
            'source' => 'typing',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['source']);
        $this->assertDatabaseCount('arrival_details', 0);
    }

    /**
     * A manual row survives alongside a scanned row of the same header.
     */
    public function test_manual_and_scanned_rows_coexist_under_one_header(): void
    {
        $header = ArrivalHeader::factory()->create();
        ArrivalDetail::factory()->create([
            'arrival_header_id' => $header->id,
            'source' => 'scan',
        ]);

        $this->postJson(route('arrivals.details.store', $header), $this->payload())->assertCreated();

        $this->assertDatabaseCount('arrival_details', 2);
        $this->assertSame(
            ['scan', 'manual'],
            $header->details()->orderBy('id')->pluck('source')->all()
        );
    }
}
