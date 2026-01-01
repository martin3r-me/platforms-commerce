<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable();
            $table->string('color')->default('#333');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('commerce_product_board_slot_id')
                ->nullable()
                ->constrained('commerce_product_board_slots', 'id')
                ->nullOnDelete()
                ->name('fk_product_board_slot');
            $table->enum('price_deviation_type', ['absolute', 'relative'])->default('absolute');
            $table->decimal('price_deviation_value', 10, 2)->default(0);
            $table->integer('order')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_products');
    }
};

