<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_slot_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_product_slot_id')
                ->constrained('commerce_product_slots', 'id')
                ->onDelete('cascade')
                ->onUpdate('cascade')
                ->index('slot_dep_slot_id');
            $table->foreignId('commerce_product_slot_variant_id')
                ->constrained('commerce_product_slot_variants', 'id')
                ->onDelete('cascade')
                ->onUpdate('cascade')
                ->index('slot_dep_variant_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_slot_dependencies');
    }
};

