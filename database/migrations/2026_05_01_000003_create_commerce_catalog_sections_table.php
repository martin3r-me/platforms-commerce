<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_catalog_sections')) {
            return;
        }

        Schema::create('commerce_catalog_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('commerce_catalog_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id', 'fk_catalog_sections_team')->references('id')->on('teams');
            $table->foreign('commerce_catalog_id', 'fk_catalog_sections_catalog')
                ->references('id')->on('commerce_catalogs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_catalog_sections');
    }
};
