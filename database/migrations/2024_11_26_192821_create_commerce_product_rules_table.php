<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_product_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_product_id')
                ->constrained('commerce_products', 'id')
                ->cascadeOnDelete()
                ->name('fk_product_rules_product');
            $table->integer('max_quantity_per_order')->nullable();
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->timestamp('sale_period_start')->nullable();
            $table->timestamp('sale_period_end')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_rules');
    }
};

