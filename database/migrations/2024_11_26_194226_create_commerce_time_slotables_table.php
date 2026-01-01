<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_time_slotables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->foreignId('modules_relations_account_id')->nullable();
            $table->foreignId('commerce_product_time_slot_id')
                ->constrained('commerce_product_time_slots', 'id')
                ->cascadeOnDelete()
                ->name('fk_time_slotables_time_slot');
            $table->unsignedBigInteger('time_slotable_id');
            $table->string('time_slotable_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_time_slotables');
    }
};

