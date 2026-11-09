<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Seed suppliers with barcode layouts that mirror real wire rod tags.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Nippon Steel',
                'code' => 'NS',
                'delimiter' => '-',
                'barcode_format' => 'manufacturer,grade,heat_no,coil_no,weight,size,qr_date',
            ],
            [
                'name' => 'POSCO',
                'code' => 'POSCO',
                'delimiter' => '|',
                'barcode_format' => 'heat_no,coil_no,manufacturer,grade,weight,size,qr_date',
            ],
            [
                'name' => 'KS Mira',
                'code' => 'KSM',
                'delimiter' => ';',
                'barcode_format' => 'manufacturer,grade,heat_no,coil_no,weight,size',
            ],
            [
                'name' => 'Gunawan Steel',
                'code' => 'GS',
                'delimiter' => '#',
                'barcode_format' => 'grade,manufacturer,heat_no,coil_no,weight,size,qr_date',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['name' => $supplier['name']],
                $supplier,
            );
        }
    }
}
