<?php

namespace Database\Seeders;

use App\Models\ArrivalDetail;
use App\Models\ArrivalHeader;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ArrivalSeeder extends Seeder
{
    /**
     * Seed sample arrival POs with scanned and manual QR tag rows.
     */
    public function run(): void
    {
        $this->call(SupplierSeeder::class);

        $nippon = Supplier::query()->where('name', 'Nippon Steel')->firstOrFail();
        $posco = Supplier::query()->where('name', 'POSCO')->firstOrFail();
        $ksMira = Supplier::query()->where('name', 'KS Mira')->firstOrFail();

        $poNippon = ArrivalHeader::query()->updateOrCreate(
            ['po_number' => 'PO-DEMO-001'],
            [
                'tanggal' => now()->subDays(2)->toDateString(),
                'item' => 'Wire Rod',
                'description' => 'Wire rod SWRH82B untuk produksi spring',
                'supplier_id' => $nippon->id,
            ],
        );

        $this->seedDetails($poNippon, [
            [
                'manufacturer' => 'Nippon Steel',
                'grade' => 'SWRH82B',
                'heat_no' => 'NS240001',
                'coil_no' => 'C001',
                'weight' => 2.145,
                'size' => 5.5,
                'qr_date' => now()->subDays(10)->toDateString(),
                'source' => 'scan',
                'scanned_at' => now()->subDays(2)->setTime(9, 15),
            ],
            [
                'manufacturer' => 'Nippon Steel',
                'grade' => 'SWRH82B',
                'heat_no' => 'NS240002',
                'coil_no' => 'C002',
                'weight' => 2.210,
                'size' => 5.5,
                'qr_date' => now()->subDays(10)->toDateString(),
                'source' => 'scan',
                'scanned_at' => now()->subDays(2)->setTime(9, 18),
            ],
            [
                'manufacturer' => 'Nippon Steel',
                'grade' => 'SWRH82B',
                'heat_no' => 'NS240003',
                'coil_no' => 'C003',
                'weight' => 2.098,
                'size' => 5.5,
                'qr_date' => now()->subDays(10)->toDateString(),
                'source' => 'manual',
                'scanned_at' => now()->subDays(2)->setTime(9, 25),
            ],
        ]);

        $poPosco = ArrivalHeader::query()->updateOrCreate(
            ['po_number' => 'PO-DEMO-002'],
            [
                'tanggal' => now()->subDay()->toDateString(),
                'item' => 'Wire Rod',
                'description' => 'Wire rod SWRH62B ukuran 6.5 mm',
                'supplier_id' => $posco->id,
            ],
        );

        $this->seedDetails($poPosco, [
            [
                'manufacturer' => 'POSCO',
                'grade' => 'SWRH62B',
                'heat_no' => 'P241101',
                'coil_no' => 'K101',
                'weight' => 1.980,
                'size' => 6.5,
                'qr_date' => now()->subDays(7)->toDateString(),
                'source' => 'scan',
                'scanned_at' => now()->subDay()->setTime(14, 2),
            ],
            [
                'manufacturer' => 'POSCO',
                'grade' => 'SWRH62B',
                'heat_no' => 'P241102',
                'coil_no' => 'K102',
                'weight' => 2.005,
                'size' => 6.5,
                'qr_date' => now()->subDays(7)->toDateString(),
                'source' => 'scan',
                'scanned_at' => now()->subDay()->setTime(14, 5),
            ],
        ]);

        $poKsMira = ArrivalHeader::query()->updateOrCreate(
            ['po_number' => 'PO-DEMO-003'],
            [
                'tanggal' => now()->toDateString(),
                'item' => 'Wire Rod',
                'description' => 'COA pending, barcode tanpa tanggal QR',
                'supplier_id' => $ksMira->id,
            ],
        );

        $this->seedDetails($poKsMira, [
            [
                'manufacturer' => 'KS Mira',
                'grade' => 'SWRM6',
                'heat_no' => 'KM5501',
                'coil_no' => 'M01',
                'weight' => 1.750,
                'size' => 8.0,
                'qr_date' => null,
                'source' => 'scan',
                'scanned_at' => now()->setTime(8, 30),
            ],
            [
                'manufacturer' => 'KS Mira',
                'grade' => 'SWRM6',
                'heat_no' => 'KM5502',
                'coil_no' => 'M02',
                'weight' => 1.820,
                'size' => 8.0,
                'qr_date' => null,
                'source' => 'manual',
                'scanned_at' => now()->setTime(8, 36),
            ],
        ]);
    }

    /**
     * Replace the demo rows for a header so the seeder stays predictable.
     *
     * @param  list<array<string, mixed>>  $details
     */
    private function seedDetails(ArrivalHeader $header, array $details): void
    {
        $header->details()->delete();

        foreach ($details as $detail) {
            ArrivalDetail::query()->create([
                ...$detail,
                'arrival_header_id' => $header->id,
            ]);
        }
    }
}
