<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commerce_articles')) {
            return;
        }

        Schema::table('commerce_articles', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_articles', 'cost_standard_id')) {
                $table->unsignedBigInteger('cost_standard_id')->nullable()->after('commerce_tax_category_id');
                $table->foreign('cost_standard_id', 'fk_articles_cost_standard')
                    ->references('id')->on('commerce_cost_standards')->nullOnDelete();
                $table->index('cost_standard_id', 'idx_articles_cost_standard');
            }
            if (!Schema::hasColumn('commerce_articles', 'cost_quantity')) {
                $table->decimal('cost_quantity', 12, 4)->nullable()->after('cost_standard_id');
            }
            if (!Schema::hasColumn('commerce_articles', 'cost_unit')) {
                $table->string('cost_unit', 10)->nullable()->after('cost_quantity');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('commerce_articles')) {
            return;
        }

        Schema::table('commerce_articles', function (Blueprint $table) {
            if (Schema::hasColumn('commerce_articles', 'cost_standard_id')) {
                $table->dropForeign('fk_articles_cost_standard');
                $table->dropIndex('idx_articles_cost_standard');
                $table->dropColumn('cost_standard_id');
            }
            foreach (['cost_quantity', 'cost_unit'] as $col) {
                if (Schema::hasColumn('commerce_articles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
