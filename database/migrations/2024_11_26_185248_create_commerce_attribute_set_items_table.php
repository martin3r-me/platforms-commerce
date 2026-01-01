<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_attribute_set_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->foreignId('commerce_attribute_set_id')
                ->constrained('commerce_attribute_sets', 'id')
                ->cascadeOnDelete()
                ->name('fk_attribute_set_items_set');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_attribute_set_items');
    }
};

