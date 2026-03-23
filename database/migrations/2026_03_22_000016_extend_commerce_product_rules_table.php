<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_product_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_product_rules', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('id');
                $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete()->name('fk_product_rules_team');
                $table->index('team_id', 'idx_product_rules_team');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'name')) {
                $table->string('name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'rule_type')) {
                $table->string('rule_type', 30)->nullable()->after('name');
                $table->index('rule_type', 'idx_product_rules_type');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('rule_type');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'priority')) {
                $table->integer('priority')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'conditions')) {
                $table->json('conditions')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'actions')) {
                $table->json('actions')->nullable()->after('conditions');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'applies_to_type')) {
                $table->string('applies_to_type')->nullable()->after('actions');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'applies_to_id')) {
                $table->unsignedBigInteger('applies_to_id')->nullable()->after('applies_to_type');
                $table->index(['applies_to_type', 'applies_to_id'], 'idx_product_rules_applies_to');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'valid_from')) {
                $table->timestamp('valid_from')->nullable()->after('applies_to_id');
            }
            if (!Schema::hasColumn('commerce_product_rules', 'valid_until')) {
                $table->timestamp('valid_until')->nullable()->after('valid_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commerce_product_rules', function (Blueprint $table) {
            $cols = ['valid_until', 'valid_from', 'applies_to_id', 'applies_to_type', 'actions', 'conditions', 'priority', 'is_active', 'rule_type', 'name', 'user_id', 'team_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('commerce_product_rules', $col)) {
                    if ($col === 'team_id') {
                        $table->dropForeign('fk_product_rules_team');
                        $table->dropIndex('idx_product_rules_team');
                    }
                    if ($col === 'rule_type') {
                        $table->dropIndex('idx_product_rules_type');
                    }
                    if ($col === 'applies_to_id') {
                        $table->dropIndex('idx_product_rules_applies_to');
                    }
                }
            }
            // Drop columns in separate statement
            $dropCols = [];
            foreach ($cols as $col) {
                if (Schema::hasColumn('commerce_product_rules', $col)) {
                    $dropCols[] = $col;
                }
            }
            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
