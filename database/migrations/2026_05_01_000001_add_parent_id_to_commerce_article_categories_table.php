<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('commerce_article_categories', 'parent_id')) {
            return;
        }

        Schema::table('commerce_article_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('team_id')
                ->constrained('commerce_article_categories', 'id', 'fk_article_categories_parent')
                ->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0)->after('color');

            $table->index(['team_id', 'parent_id'], 'idx_article_categories_team_parent');
        });
    }

    public function down(): void
    {
        Schema::table('commerce_article_categories', function (Blueprint $table) {
            $table->dropIndex('idx_article_categories_team_parent');
            $table->dropForeign('fk_article_categories_parent');
            $table->dropColumn(['parent_id', 'sort_order']);
        });
    }
};
