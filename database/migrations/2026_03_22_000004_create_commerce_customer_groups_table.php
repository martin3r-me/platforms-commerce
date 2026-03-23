<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_customer_groups')) { return; }
        Schema::create('commerce_customer_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_customer_groups_team');
            $table->index('team_id', 'idx_customer_groups_team');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_customer_groups');
    }
};
