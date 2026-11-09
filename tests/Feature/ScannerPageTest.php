<?php

namespace Tests\Feature;

use App\Models\ArrivalHeader;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScannerPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The home page displays the QR tag scanner.
     */
    public function test_home_page_displays_qr_tag_scanner(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('QR Tag Scanner Wire Rod');
        $response->assertSee('Heat No');
        $response->assertViewIs('scanner');
    }

    /**
     * The header form is shown before any PO is saved.
     */
    public function test_home_page_shows_the_header_form(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Header Arrival');
        $response->assertSee('Purchase Order');
        $response->assertSee('Tanggal');
        $response->assertSee('Item');
        $response->assertSee('Description');
        $response->assertSee('Supplier');
        $response->assertSee('Simpan Header');
        $response->assertSee('Riwayat PO');
        $response->assertSee('Belum ada PO tersimpan.');
    }

    /**
     * Scanning stays locked until a header has been saved.
     */
    public function test_home_page_locks_the_scan_button_without_a_header(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="btnScan" onclick="toggleScanner()" disabled', false);
    }

    /**
     * The newest header opens as the active PO with an unlocked scan button.
     */
    public function test_home_page_opens_the_latest_header_as_active(): void
    {
        $header = ArrivalHeader::factory()->create(['po_number' => 'PO-AKTIF-01']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('PO-AKTIF-01');
        $response->assertSee('PO Aktif');
        $response->assertDontSee('id="btnScan" onclick="toggleScanner()" disabled', false);
    }

    /**
     * The header form picks the supplier whose barcode rules apply to the PO.
     */
    public function test_home_page_offers_a_supplier_selector(): void
    {
        Supplier::factory()->create(['name' => 'PT Sinar Baja']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('name="supplier_id"', false);
        $response->assertSee('PT Sinar Baja');
        $response->assertSee('Kelola Supplier');
    }

    /**
     * Without any supplier the header form points at the management page.
     */
    public function test_home_page_points_to_supplier_management_when_none_exist(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Belum ada supplier');
        $response->assertSee(route('suppliers.index'));
    }
}
