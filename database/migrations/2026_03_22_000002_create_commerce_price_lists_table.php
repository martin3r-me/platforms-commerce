<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_price_lists')) { return; }
        Schema::create('commerce_price_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('price_type', 30)->default('standard');
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_price_lists_team');
            $table->index('team_id', 'idx_price_lists_team');
            $table->index('price_type', 'idx_price_lists_type');
            $table->index('priority', 'idx_price_lists_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_price_lists');
    }
};
