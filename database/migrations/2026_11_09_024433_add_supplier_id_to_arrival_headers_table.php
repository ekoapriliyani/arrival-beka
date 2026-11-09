<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The barcode layout used by suppliers that have no explicit format yet.
     */
    private const DEFAULT_FORMAT = 'manufacturer,grade,heat_no,coil_no,weight,size,qr_date';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('arrival_headers', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('item')->constrained()->restrictOnDelete();
        });

        $this->moveLegacySupplierNames();

        Schema::table('arrival_headers', function (Blueprint $table) {
            $table->dropColumn('supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arrival_headers', function (Blueprint $table) {
            $table->string('supplier')->nullable()->after('supplier_id');
        });

        $this->restoreLegacySupplierNames();

        Schema::table('arrival_headers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });
    }

    /**
     * Turn every free text supplier already stored on a header into a supplier row.
     */
    private function moveLegacySupplierNames(): void
    {
        $headers = DB::table('arrival_headers')->orderBy('id')->get();

        foreach ($headers as $header) {
            $name = trim((string) $header->supplier);

            if ($name === '') {
                continue;
            }

            $supplierId = DB::table('suppliers')->where('name', $name)->value('id')
                ?? DB::table('suppliers')->insertGetId([
                    'name' => $name,
                    'delimiter' => '-',
                    'barcode_format' => self::DEFAULT_FORMAT,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('arrival_headers')->where('id', $header->id)->update(['supplier_id' => $supplierId]);
        }
    }

    /**
     * Put the supplier name back on each header so the column can be restored.
     */
    private function restoreLegacySupplierNames(): void
    {
        $suppliers = DB::table('suppliers')->pluck('name', 'id');

        foreach (DB::table('arrival_headers')->orderBy('id')->get() as $header) {
            DB::table('arrival_headers')->where('id', $header->id)->update([
                'supplier' => $suppliers[$header->supplier_id] ?? null,
            ]);
        }
    }
};
