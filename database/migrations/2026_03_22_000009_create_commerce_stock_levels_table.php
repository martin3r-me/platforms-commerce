<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_stock_levels')) { return; }
        Schema::create('commerce_stock_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('commerce_article_id');
            $table->unsignedBigInteger('commerce_warehouse_id');
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('reserved_quantity', 12, 4)->default(0);
            $table->decimal('minimum_quantity', 12, 4)->nullable();
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_stock_levels_team');
            $table->foreign('commerce_article_id')->references('id')->on('commerce_articles')->cascadeOnDelete()->name('fk_stock_levels_article');
            $table->foreign('commerce_warehouse_id')->references('id')->on('commerce_warehouses')->cascadeOnDelete()->name('fk_stock_levels_warehouse');
            $table->unique(['commerce_article_id', 'commerce_warehouse_id'], 'uq_stock_levels_article_warehouse');
            $table->index('team_id', 'idx_stock_levels_team');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_stock_levels');
    }
};
