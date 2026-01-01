<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_product_slot_dimension_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_product_slot_dimension_id')->constrained('commerce_product_slot_dimensions')->onDelete('cascade')->name('dimension_id_index');
            $table->string('value');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_slot_dimension_values');
    }
};

