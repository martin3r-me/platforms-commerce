<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_articles', function (Blueprint $table) {
            $table->foreignId('commerce_article_type_id')
                ->nullable()
                ->after('commerce_tax_category_id')
                ->constrained('commerce_article_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commerce_articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('commerce_article_type_id');
        });
    }
};
