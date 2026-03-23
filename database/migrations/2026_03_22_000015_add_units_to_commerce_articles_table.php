<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_articles', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_articles', 'commerce_sales_unit_id')) {
                $table->unsignedBigInteger('commerce_sales_unit_id')->nullable()->after('commerce_article_type_id');
                $table->foreign('commerce_sales_unit_id')->references('id')->on('commerce_units')->nullOnDelete()->name('fk_articles_sales_unit');
            }
            if (!Schema::hasColumn('commerce_articles', 'commerce_storage_unit_id')) {
                $table->unsignedBigInteger('commerce_storage_unit_id')->nullable()->after('commerce_sales_unit_id');
                $table->foreign('commerce_storage_unit_id')->references('id')->on('commerce_units')->nullOnDelete()->name('fk_articles_storage_unit');
            }
            if (!Schema::hasColumn('commerce_articles', 'sales_to_storage_factor')) {
                $table->decimal('sales_to_storage_factor', 18, 8)->nullable()->after('commerce_storage_unit_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commerce_articles', function (Blueprint $table) {
            if (Schema::hasColumn('commerce_articles', 'sales_to_storage_factor')) {
                $table->dropColumn('sales_to_storage_factor');
            }
            if (Schema::hasColumn('commerce_articles', 'commerce_storage_unit_id')) {
                $table->dropForeign('fk_articles_storage_unit');
                $table->dropColumn('commerce_storage_unit_id');
            }
            if (Schema::hasColumn('commerce_articles', 'commerce_sales_unit_id')) {
                $table->dropForeign('fk_articles_sales_unit');
                $table->dropColumn('commerce_sales_unit_id');
            }
        });
    }
};
