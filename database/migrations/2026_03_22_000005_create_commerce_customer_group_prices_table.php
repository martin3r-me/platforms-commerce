<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_customer_group_prices')) { return; }
        Schema::create('commerce_customer_group_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('commerce_customer_group_id');
            $table->unsignedBigInteger('commerce_article_id');
            $table->unsignedBigInteger('commerce_price_list_id')->nullable();
            $table->decimal('price', 12, 4);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_cust_group_prices_team');
            $table->foreign('commerce_customer_group_id')->references('id')->on('commerce_customer_groups')->cascadeOnDelete()->name('fk_cust_group_prices_group');
            $table->foreign('commerce_article_id')->references('id')->on('commerce_articles')->cascadeOnDelete()->name('fk_cust_group_prices_article');
            $table->foreign('commerce_price_list_id')->references('id')->on('commerce_price_lists')->nullOnDelete()->name('fk_cust_group_prices_list');
            $table->index('team_id', 'idx_cust_group_prices_team');
            $table->index(['commerce_customer_group_id', 'commerce_article_id'], 'idx_cust_group_prices_group_article');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_customer_group_prices');
    }
};
