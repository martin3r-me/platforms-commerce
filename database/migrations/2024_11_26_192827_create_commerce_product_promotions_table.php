<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_product_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_product_id')
                ->constrained('commerce_products', 'id')
                ->cascadeOnDelete()
                ->name('fk_product_promotions_product');
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('min_cart_value', 10, 2)->nullable();
            $table->timestamp('promotion_start')->nullable();
            $table->timestamp('promotion_end')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_promotions');
    }
};

