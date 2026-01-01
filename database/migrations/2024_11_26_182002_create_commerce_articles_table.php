
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_articles')) {
            return;
        }

        Schema::create('commerce_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->foreignId('modules_relations_account_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('gtin', 14)->nullable()->unique();
            $table->string('ean', 13)->nullable()->unique();
            $table->string('upc', 12)->nullable()->unique();
            $table->string('isbn', 13)->nullable()->unique();
            $table->foreignId('commerce_manufacturer_id')
                ->nullable()
                ->constrained('commerce_manufacturers', 'id')
                ->nullOnDelete()
                ->name('fk_article_manufacturer');
            $table->string('manufacturer_part_number')->nullable();
            $table->string('country_of_origin', 2)->nullable();
            $table->string('hs_code')->nullable();

            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('base_price_quantity', 10, 2)->nullable();
            $table->string('base_price_unit')->nullable();
            $table->foreignId('commerce_tax_category_id')->nullable();

            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('depth', 10, 2)->nullable();
            $table->decimal('volume', 10, 2)->nullable();
            $table->boolean('is_fragile')->default(false);
            $table->string('shipping_class')->nullable();
            $table->integer('lead_time_days')->nullable();

            $table->boolean('is_available')->default(true);
            $table->integer('stock_level')->nullable();
            $table->integer('stock_alert_threshold')->nullable();
            $table->boolean('backorder_allowed')->default(false);

            $table->boolean('is_hazardous')->default(false);
            $table->date('expiry_date')->nullable();
            $table->decimal('storage_temperature', 5, 2)->nullable();
            $table->boolean('recyclable')->default(false);

            $table->foreignId('category_id')->nullable()->constrained('commerce_article_categories')->nullOnDelete();
            $table->json('tags')->nullable();
            $table->boolean('is_digital')->default(false);
            $table->boolean('is_physical')->default(true);

            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->json('product_highlights')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_articles');
    }
};
