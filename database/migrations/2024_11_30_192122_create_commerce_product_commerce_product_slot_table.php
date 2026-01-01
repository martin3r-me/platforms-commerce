
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('commerce_product_commerce_product_slot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commerce_product_id');
            $table->foreign('commerce_product_id', 'fk_product_slot_product')
                ->references('id')
                ->on('commerce_products')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedBigInteger('commerce_product_slot_id');
            $table->foreign('commerce_product_slot_id', 'fk_product_slot_slot')
                ->references('id')
                ->on('commerce_product_slots')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_commerce_product_slot');
    }
};
