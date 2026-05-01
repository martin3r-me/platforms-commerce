<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_supplier_field_mappings')) {
            return;
        }

        Schema::create('commerce_supplier_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_supplier_id')
                ->constrained('commerce_suppliers')
                ->cascadeOnDelete()
                ->name('fk_supplier_field_mappings_supplier');
            $table->string('source_key');
            $table->string('target_field')->nullable();
            $table->string('label')->nullable();
            $table->string('data_type')->default('string');
            $table->string('transform')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('commerce_supplier_id', 'idx_supplier_field_mappings_supplier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_supplier_field_mappings');
    }
};
