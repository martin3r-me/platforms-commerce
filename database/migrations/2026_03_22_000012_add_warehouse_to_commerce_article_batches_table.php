<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_article_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_article_batches', 'commerce_warehouse_id')) {
                $table->unsignedBigInteger('commerce_warehouse_id')->nullable()->after('commerce_supplier_id');
                $table->foreign('commerce_warehouse_id')->references('id')->on('commerce_warehouses')->nullOnDelete()->name('fk_article_batches_warehouse');
            }
            if (!Schema::hasColumn('commerce_article_batches', 'status')) {
                $table->string('status', 20)->default('active')->after('commerce_warehouse_id');
            }
            if (!Schema::hasColumn('commerce_article_batches', 'remaining_quantity')) {
                $table->decimal('remaining_quantity', 12, 4)->nullable()->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commerce_article_batches', function (Blueprint $table) {
            if (Schema::hasColumn('commerce_article_batches', 'remaining_quantity')) {
                $table->dropColumn('remaining_quantity');
            }
            if (Schema::hasColumn('commerce_article_batches', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('commerce_article_batches', 'commerce_warehouse_id')) {
                $table->dropForeign('fk_article_batches_warehouse');
                $table->dropColumn('commerce_warehouse_id');
            }
        });
    }
};
