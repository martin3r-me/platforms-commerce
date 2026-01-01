
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_product_slot_dimensions')) {
            return;
        }

        
        Schema::create('commerce_product_slot_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_product_slot_id')->constrained('commerce_product_slots')->onDelete('cascade')->index('slot_dimensions_slot_fk');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_slot_dimensions');
    }
};
