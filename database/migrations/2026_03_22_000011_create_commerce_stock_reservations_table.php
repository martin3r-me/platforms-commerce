<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_stock_reservations')) { return; }
        Schema::create('commerce_stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('commerce_article_id');
            $table->unsignedBigInteger('commerce_warehouse_id');
            $table->decimal('quantity', 12, 4);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_stock_reservations_team');
            $table->foreign('commerce_article_id')->references('id')->on('commerce_articles')->cascadeOnDelete()->name('fk_stock_reservations_article');
            $table->foreign('commerce_warehouse_id')->references('id')->on('commerce_warehouses')->cascadeOnDelete()->name('fk_stock_reservations_warehouse');
            $table->index('team_id', 'idx_stock_reservations_team');
            $table->index('expires_at', 'idx_stock_reservations_expires');
            $table->index(['reference_type', 'reference_id'], 'idx_stock_reservations_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_stock_reservations');
    }
};
