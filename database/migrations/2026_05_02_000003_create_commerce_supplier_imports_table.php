<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_supplier_imports')) {
            return;
        }

        Schema::create('commerce_supplier_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_supplier_id')
                ->constrained('commerce_suppliers')
                ->cascadeOnDelete()
                ->name('fk_supplier_imports_supplier');
            $table->string('status')->default('pending');
            $table->integer('rows_received')->default(0);
            $table->integer('rows_created')->default(0);
            $table->integer('rows_updated')->default(0);
            $table->integer('rows_skipped')->default(0);
            $table->json('error_log')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->longText('raw_payload')->nullable();
            $table->timestamps();

            $table->index('commerce_supplier_id', 'idx_supplier_imports_supplier');
            $table->index('status', 'idx_supplier_imports_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_supplier_imports');
    }
};
