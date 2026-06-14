<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commerce_article_supplier')) {
            return;
        }

        Schema::table('commerce_article_supplier', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_article_supplier', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 4)->nullable()->after('external_id');
            }
            if (!Schema::hasColumn('commerce_article_supplier', 'purchase_currency')) {
                $table->string('purchase_currency', 3)->default('EUR')->after('purchase_price');
            }
            if (!Schema::hasColumn('commerce_article_supplier', 'valid_from')) {
                $table->date('valid_from')->nullable()->after('purchase_currency');
            }
            if (!Schema::hasColumn('commerce_article_supplier', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('valid_from');
            }
            if (!Schema::hasColumn('commerce_article_supplier', 'is_preferred')) {
                $table->boolean('is_preferred')->default(false)->after('valid_until');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('commerce_article_supplier')) {
            return;
        }

        Schema::table('commerce_article_supplier', function (Blueprint $table) {
            foreach (['is_preferred', 'valid_until', 'valid_from', 'purchase_currency', 'purchase_price'] as $col) {
                if (Schema::hasColumn('commerce_article_supplier', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
