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
        Schema::table('arrival_details', function (Blueprint $table) {
            $table->unique(['arrival_header_id', 'coil_no'], 'arrival_details_header_coil_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arrival_details', function (Blueprint $table) {
            $table->dropUnique('arrival_details_header_coil_unique');
        });
    }
};
