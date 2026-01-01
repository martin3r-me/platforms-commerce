
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_product_time_slots')) {
            return;
        }

        
        Schema::create('commerce_product_time_slots', function (Blueprint $table) {
            $table->id();
            $table->time('start');
            $table->time('end');
            $table->unsignedTinyInteger('day_of_week');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_time_slots');
    }
};
