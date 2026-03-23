<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_sales_contexts', function (Blueprint $table) {
            if (!Schema::hasColumn('commerce_sales_contexts', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('priority');
            }
            if (!Schema::hasColumn('commerce_sales_contexts', 'settings')) {
                $table->json('settings')->nullable()->after('is_default');
            }
        });

        if (!Schema::hasColumn('commerce_sales', 'commerce_sales_context_id')) {
            Schema::table('commerce_sales', function (Blueprint $table) {
                $table->unsignedBigInteger('commerce_sales_context_id')->nullable()->after('team_id');
                $table->foreign('commerce_sales_context_id')->references('id')->on('commerce_sales_contexts')->nullOnDelete()->name('fk_sales_sales_context');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('commerce_sales', 'commerce_sales_context_id')) {
            Schema::table('commerce_sales', function (Blueprint $table) {
                $table->dropForeign('fk_sales_sales_context');
                $table->dropColumn('commerce_sales_context_id');
            });
        }
        Schema::table('commerce_sales_contexts', function (Blueprint $table) {
            if (Schema::hasColumn('commerce_sales_contexts', 'settings')) {
                $table->dropColumn('settings');
            }
            if (Schema::hasColumn('commerce_sales_contexts', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
