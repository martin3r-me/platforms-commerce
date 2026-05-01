<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_article_supplier')) {
            return;
        }

        Schema::create('commerce_article_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')
                ->constrained('commerce_articles')
                ->cascadeOnDelete()
                ->name('fk_article_supplier_article');
            $table->foreignId('supplier_id')
                ->constrained('commerce_suppliers')
                ->cascadeOnDelete()
                ->name('fk_article_supplier_supplier');
            $table->string('external_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'external_id'], 'uq_article_supplier_ext');
            $table->index('article_id', 'idx_article_supplier_article');
            $table->index('supplier_id', 'idx_article_supplier_supplier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_article_supplier');
    }
};
