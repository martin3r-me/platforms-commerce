<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_stock_movements')) { return; }
        Schema::create('commerce_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('commerce_article_id');
            $table->unsignedBigInteger('commerce_warehouse_id');
            $table->unsignedBigInteger('target_warehouse_id')->nullable();
            $table->string('type', 30);
            $table->decimal('quantity', 12, 4);
            $table->text('reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_stock_movements_team');
            $table->foreign('commerce_article_id')->references('id')->on('commerce_articles')->cascadeOnDelete()->name('fk_stock_movements_article');
            $table->foreign('commerce_warehouse_id')->references('id')->on('commerce_warehouses')->cascadeOnDelete()->name('fk_stock_movements_warehouse');
            $table->foreign('target_warehouse_id')->references('id')->on('commerce_warehouses')->nullOnDelete()->name('fk_stock_movements_target_wh');
            $table->index('team_id', 'idx_stock_movements_team');
            $table->index('type', 'idx_stock_movements_type');
            $table->index(['reference_type', 'reference_id'], 'idx_stock_movements_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_stock_movements');
    }
};
