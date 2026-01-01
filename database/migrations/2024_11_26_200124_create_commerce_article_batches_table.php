
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_article_batches')) {
            return;
        }

        
        Schema::create('commerce_article_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_article_id')
                ->constrained('commerce_articles', 'id')
                ->cascadeOnDelete()
                ->name('fk_article_batches_article');
            $table->foreignId('commerce_supplier_id')
                ->nullable()
                ->constrained('commerce_suppliers', 'id')
                ->nullOnDelete()
                ->name('fk_batches_supplier');
            $table->string('batch_number')->unique();
            $table->integer('quantity');
            $table->string('storage_location')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_article_batches');
    }
};
