<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_unit_conversions')) { return; }
        Schema::create('commerce_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('from_unit_id');
            $table->unsignedBigInteger('to_unit_id');
            $table->decimal('factor', 18, 8);
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_unit_conversions_team');
            $table->foreign('from_unit_id')->references('id')->on('commerce_units')->cascadeOnDelete()->name('fk_unit_conversions_from');
            $table->foreign('to_unit_id')->references('id')->on('commerce_units')->cascadeOnDelete()->name('fk_unit_conversions_to');
            $table->unique(['from_unit_id', 'to_unit_id'], 'uq_unit_conversions_from_to');
            $table->index('team_id', 'idx_unit_conversions_team');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_unit_conversions');
    }
};
