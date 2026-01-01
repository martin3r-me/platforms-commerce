<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_sale_id')
                ->constrained('commerce_sales')
                ->cascadeOnDelete();
            $table->foreignId('commerce_product_id')
                ->constrained('commerce_products', 'id')
                ->cascadeOnDelete()
                ->name('fk_sale_items_product');
            $table->foreignId('commerce_article_batch_id')
                ->nullable()
                ->constrained('commerce_article_batches', 'id')
                ->nullOnDelete()
                ->name('fk_sale_items_batch');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_sale_items');
    }
};

