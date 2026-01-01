
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_product_slot_variant_dimension_values')) {
            return;
        }

        
        Schema::create('commerce_product_slot_variant_dimension_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commerce_product_slot_variant_id');
            $table->foreign('commerce_product_slot_variant_id')
                ->references('id')
                ->on('commerce_product_slot_variants')
                ->onDelete('cascade')->name('fk_variant_dimension_values_variant');
            $table->unsignedBigInteger('commerce_product_slot_dimension_value_id');
            $table->foreign('commerce_product_slot_dimension_value_id')
                ->references('id')
                ->on('commerce_product_slot_dimension_values')
                ->onDelete('cascade')->name('fk_variant_dimension_values_value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_slot_variant_dimension_values');
    }
};
