
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_product_board_slots')) {
            return;
        }

        
        Schema::create('commerce_product_board_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->string('color')->default('#333');
            $table->foreignId('commerce_product_board_id')
                ->constrained('commerce_product_boards', 'id')
                ->cascadeOnDelete()
                ->name('fk_board_slots_board');
            $table->integer('order')->default(0);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_board_slots');
    }
};
