<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('commerce_suppliers', 'source_type')) {
            Schema::table('commerce_suppliers', function (Blueprint $table) {
                $table->string('source_type')->nullable()->after('description');
                $table->string('endpoint_token', 64)->unique()->nullable()->after('source_type');
                $table->text('pull_url')->nullable()->after('endpoint_token');
                $table->text('pull_headers')->nullable()->after('pull_url');
                $table->string('pull_schedule')->nullable()->after('pull_headers');
                $table->string('natural_key')->default('sku')->after('pull_schedule');
                $table->string('status')->default('onboarding')->after('natural_key');
                $table->json('metadata')->nullable()->after('status');
                $table->timestamp('last_import_at')->nullable()->after('metadata');

                $table->index('source_type', 'idx_commerce_suppliers_source_type');
                $table->index('status', 'idx_commerce_suppliers_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('commerce_suppliers', 'source_type')) {
            Schema::table('commerce_suppliers', function (Blueprint $table) {
                $table->dropIndex('idx_commerce_suppliers_source_type');
                $table->dropIndex('idx_commerce_suppliers_status');
                $table->dropColumn([
                    'source_type', 'endpoint_token', 'pull_url', 'pull_headers',
                    'pull_schedule', 'natural_key', 'status', 'metadata', 'last_import_at',
                ]);
            });
        }
    }
};
