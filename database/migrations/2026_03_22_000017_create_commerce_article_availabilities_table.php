<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_article_availabilities')) { return; }
        Schema::create('commerce_article_availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('commerce_article_id');
            $table->unsignedBigInteger('commerce_sales_context_id');
            $table->boolean('is_available')->default(true);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->decimal('max_quantity', 12, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_article_avail_team');
            $table->foreign('commerce_article_id')->references('id')->on('commerce_articles')->cascadeOnDelete()->name('fk_article_avail_article');
            $table->foreign('commerce_sales_context_id')->references('id')->on('commerce_sales_contexts')->cascadeOnDelete()->name('fk_article_avail_context');
            $table->unique(['commerce_article_id', 'commerce_sales_context_id'], 'uq_article_avail_article_context');
            $table->index('team_id', 'idx_article_avail_team');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_article_availabilities');
    }
};
