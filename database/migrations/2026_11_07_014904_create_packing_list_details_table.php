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
        Schema::create('packing_list_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_header_id')->constrained()->cascadeOnDelete();
            $table->string('manufacturer');
            $table->string('grade');
            $table->string('heat_no');
            $table->string('coil_no');
            $table->decimal('weight', 12, 3);
            $table->decimal('size', 10, 2);
            $table->date('qr_date')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_list_details');
    }
};
