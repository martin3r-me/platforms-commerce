<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_products', function (Blueprint $table) {
            $table->text('short_description')->after('description')->nullable();
            $table->text('long_description')->after('short_description')->nullable();
            $table->json('product_highlights')->after('long_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('commerce_products', function (Blueprint $table) {
            $table->dropColumn('short_description');
            $table->dropColumn('long_description');
            $table->dropColumn('product_highlights');
        });
    }
};

