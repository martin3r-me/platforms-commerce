<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_catalog_section_boards')) {
            return;
        }

        Schema::create('commerce_catalog_section_boards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commerce_catalog_section_id');
            $table->unsignedBigInteger('commerce_product_board_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('commerce_catalog_section_id', 'fk_csb_section')
                ->references('id')->on('commerce_catalog_sections')->cascadeOnDelete();
            $table->foreign('commerce_product_board_id', 'fk_csb_board')
                ->references('id')->on('commerce_product_boards')->cascadeOnDelete();
            $table->unique(['commerce_catalog_section_id', 'commerce_product_board_id'], 'uq_csb_section_board');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_catalog_section_boards');
    }
};
