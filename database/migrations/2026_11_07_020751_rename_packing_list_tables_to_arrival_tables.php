<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('packing_list_headers', 'arrival_headers');
        Schema::rename('packing_list_details', 'arrival_details');

        Schema::table('arrival_details', function (Blueprint $table) {
            $table->renameColumn('packing_list_header_id', 'arrival_header_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arrival_details', function (Blueprint $table) {
            $table->renameColumn('arrival_header_id', 'packing_list_header_id');
        });

        Schema::rename('arrival_details', 'packing_list_details');
        Schema::rename('arrival_headers', 'packing_list_headers');
    }
};
