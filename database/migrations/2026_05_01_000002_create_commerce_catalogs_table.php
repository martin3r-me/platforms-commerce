<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_catalogs')) {
            return;
        }

        Schema::create('commerce_catalogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug');
            $table->string('status', 30)->default('draft');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id', 'fk_catalogs_team')->references('id')->on('teams');
            $table->unique(['team_id', 'slug'], 'uq_catalogs_team_slug');
            $table->index('team_id', 'idx_catalogs_team');
            $table->index('status', 'idx_catalogs_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_catalogs');
    }
};
