
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_tax_rules')) {
            return;
        }

        
        Schema::create('commerce_tax_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->unsignedBigInteger('commerce_sales_context_id');
            $table->unsignedBigInteger('commerce_tax_category_id');
            $table->decimal('tax_rate', 5, 2);
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('commerce_sales_context_id', 'fk_article_price_sales_context')
                ->references('id')->on('commerce_sales_contexts')
                ->onDelete('cascade');

            $table->foreign('commerce_tax_category_id', 'fk_article_price_tax_category')
                ->references('id')->on('commerce_tax_categories')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_tax_rules');
    }
};
