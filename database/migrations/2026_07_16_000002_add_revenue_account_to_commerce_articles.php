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

        if (Schema::hasColumn('commerce_articles', 'revenue_account')) {
            return;
        }

        Schema::table('commerce_articles', function (Blueprint $table) {
            // Optionales Erlöskonto-Override auf Artikelebene. Wenn gesetzt, überschreibt
            // es das Standard-Erlöskonto der Steuerkategorie (revenue_account dort).
            // Nur setzen, wenn der Artikel fibutechnisch von seiner Steuerkategorie abweicht.
            $table->string('revenue_account', 20)->nullable()->after('commerce_tax_category_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('commerce_articles')) {
            return;
        }

        if (!Schema::hasColumn('commerce_articles', 'revenue_account')) {
            return;
        }

        Schema::table('commerce_articles', function (Blueprint $table) {
            $table->dropColumn('revenue_account');
        });
    }
};
