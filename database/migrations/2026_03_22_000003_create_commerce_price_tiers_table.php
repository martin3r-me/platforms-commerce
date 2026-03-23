<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_price_tiers')) { return; }
        Schema::create('commerce_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('commerce_price_list_id');
            $table->unsignedBigInteger('commerce_article_id');
            $table->decimal('min_quantity', 12, 4)->default(1);
            $table->decimal('max_quantity', 12, 4)->nullable();
            $table->decimal('price', 12, 4);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_price_tiers_team');
            $table->foreign('commerce_price_list_id')->references('id')->on('commerce_price_lists')->cascadeOnDelete()->name('fk_price_tiers_price_list');
            $table->foreign('commerce_article_id')->references('id')->on('commerce_articles')->cascadeOnDelete()->name('fk_price_tiers_article');
            $table->index('team_id', 'idx_price_tiers_team');
            $table->index(['commerce_price_list_id', 'commerce_article_id'], 'idx_price_tiers_list_article');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_price_tiers');
    }
};
