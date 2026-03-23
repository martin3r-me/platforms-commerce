<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_units')) { return; }
        Schema::create('commerce_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('symbol', 20);
            $table->string('type', 20);
            $table->boolean('is_base_unit')->default(false);
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->decimal('factor_to_base', 18, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_units_team');
            $table->foreign('base_unit_id')->references('id')->on('commerce_units')->nullOnDelete()->name('fk_units_base_unit');
            $table->index('team_id', 'idx_units_team');
            $table->index('type', 'idx_units_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_units');
    }
};
