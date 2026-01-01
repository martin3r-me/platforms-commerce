
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_product_slot_variants')) {
            return;
        }

        
        Schema::create('commerce_product_slot_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commerce_product_slot_id');
            $table->foreign('commerce_product_slot_id', 'fk_slot_variants_slot')
                ->references('id')
                ->on('commerce_product_slots')
                ->onDelete('cascade');
            $table->unsignedBigInteger('commerce_article_id')->nullable();
            $table->foreign('commerce_article_id', 'fk_slot_variants_article')
                ->references('id')
                ->on('commerce_articles')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_product_slot_variants');
    }
};
