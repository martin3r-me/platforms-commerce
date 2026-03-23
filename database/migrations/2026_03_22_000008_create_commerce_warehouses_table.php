<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_warehouses')) { return; }
        Schema::create('commerce_warehouses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_warehouses_team');
            $table->index('team_id', 'idx_warehouses_team');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_warehouses');
    }
};
