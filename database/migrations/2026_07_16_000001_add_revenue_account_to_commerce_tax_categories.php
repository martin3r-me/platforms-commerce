<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commerce_tax_categories')) {
            return;
        }

        if (Schema::hasColumn('commerce_tax_categories', 'revenue_account')) {
            return;
        }

        Schema::table('commerce_tax_categories', function (Blueprint $table) {
            // Standard-Erlöskonto (Fibu-/Sachkonto) für Umsätze dieser Steuerkategorie.
            // Z.B. SKR03 8400 (Erlöse 19%), 8300 (Erlöse 7%). String wegen führender
            // Nullen und DATEV-Kontenrahmen-Erweiterung.
            $table->string('revenue_account', 20)->nullable()->after('default_rate');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('commerce_tax_categories')) {
            return;
        }

        if (!Schema::hasColumn('commerce_tax_categories', 'revenue_account')) {
            return;
        }

        Schema::table('commerce_tax_categories', function (Blueprint $table) {
            $table->dropColumn('revenue_account');
        });
    }
};
