<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_sales_contexts', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_sales_contexts', 'channel_type')) {
                $table->string('channel_type', 30)->nullable()->after('description');
                $table->index('channel_type', 'idx_sales_contexts_channel');
            }
            if (!Schema::hasColumn('commerce_sales_contexts', 'priority')) {
                $table->integer('priority')->default(0)->after('channel_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commerce_sales_contexts', function (Blueprint $table) {
            if (Schema::hasColumn('commerce_sales_contexts', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('commerce_sales_contexts', 'channel_type')) {
                $table->dropIndex('idx_sales_contexts_channel');
                $table->dropColumn('channel_type');
            }
        });
    }
};
