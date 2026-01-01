
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_article_commerce_attribute_set')) {
            return;
        }

        
        Schema::create('commerce_article_commerce_attribute_set', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_article_id')
                ->constrained('commerce_articles', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->name('fk_article_attribute_set_article');
            $table->foreignId('commerce_attribute_set_id')
                ->constrained('commerce_attribute_sets', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->name('fk_article_attribute_set_set');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_article_commerce_attribute_set');
    }
};
