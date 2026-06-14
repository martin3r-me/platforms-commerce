<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_cost_standards')) {
            return;
        }

        Schema::create('commerce_cost_standards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('cost_per_hour', 12, 4)->nullable();
            $table->decimal('cost_per_day', 12, 4)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('color', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id', 'fk_cost_standards_team')
                ->references('id')->on('teams')->nullOnDelete();

            $table->index('team_id', 'idx_cost_standards_team');
            $table->index('is_active', 'idx_cost_standards_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_cost_standards');
    }
};
