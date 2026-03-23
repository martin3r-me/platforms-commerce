<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_article_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_article_prices', 'commerce_price_list_id')) {
                $table->unsignedBigInteger('commerce_price_list_id')->nullable()->after('commerce_tax_category_id');
                $table->foreign('commerce_price_list_id')->references('id')->on('commerce_price_lists')->nullOnDelete()->name('fk_article_prices_price_list');
            }
            if (!Schema::hasColumn('commerce_article_prices', 'price_type')) {
                $table->string('price_type', 30)->default('standard')->after('commerce_price_list_id');
                $table->index('price_type', 'idx_article_prices_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commerce_article_prices', function (Blueprint $table) {
            if (Schema::hasColumn('commerce_article_prices', 'price_type')) {
                $table->dropIndex('idx_article_prices_type');
                $table->dropColumn('price_type');
            }
            if (Schema::hasColumn('commerce_article_prices', 'commerce_price_list_id')) {
                $table->dropForeign('fk_article_prices_price_list');
                $table->dropColumn('commerce_price_list_id');
            }
        });
    }
};
