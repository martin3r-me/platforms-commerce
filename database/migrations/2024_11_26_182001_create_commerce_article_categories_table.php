<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_article_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('commerce_article_categories')->insert([
            ['name' => 'Food', 'description' => 'Essbare Artikel'],
            ['name' => 'NonFood', 'description' => 'Nicht essbare Artikel'],
            ['name' => 'Beverage', 'description' => 'Getränke'],
            ['name' => 'Service', 'description' => 'Service'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_article_categories');
    }
};

