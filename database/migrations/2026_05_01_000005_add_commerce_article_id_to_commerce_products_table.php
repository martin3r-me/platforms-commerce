<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('commerce_products', 'commerce_article_id')) {
            Schema::table('commerce_products', function (Blueprint $table) {
                $table->foreignId('commerce_article_id')
                    ->nullable()
                    ->after('team_id')
                    ->constrained('commerce_articles')
                    ->name('fk_products_article')
                    ->nullOnDelete();

                $table->index('commerce_article_id', 'idx_products_article');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('commerce_products', 'commerce_article_id')) {
            Schema::table('commerce_products', function (Blueprint $table) {
                $table->dropForeign('fk_products_article');
                $table->dropIndex('idx_products_article');
                $table->dropColumn('commerce_article_id');
            });
        }
    }
};
