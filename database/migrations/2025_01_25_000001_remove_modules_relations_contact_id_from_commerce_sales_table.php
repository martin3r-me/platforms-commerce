<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_sales')) {
            Schema::table('commerce_sales', function (Blueprint $table) {
                if (Schema::hasColumn('commerce_sales', 'modules_relations_contact_id')) {
                    $table->dropColumn('modules_relations_contact_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('commerce_sales')) {
            Schema::table('commerce_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('commerce_sales', 'modules_relations_contact_id')) {
                    $table->unsignedBigInteger('modules_relations_contact_id')->nullable()->after('team_id');
                }
            });
        }
    }
};
