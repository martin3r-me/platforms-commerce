<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('commerce_articles', 'status')) {
            return;
        }
        Schema::table('commerce_articles', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('name')->index('idx_articles_status');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('commerce_articles', 'status')) {
            return;
        }
        Schema::table('commerce_articles', function (Blueprint $table) {
            $table->dropIndex('idx_articles_status');
            $table->dropColumn('status');
        });
    }
};
