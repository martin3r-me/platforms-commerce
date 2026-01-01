
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_attribute_sets')) {
            return;
        }

        Schema::create('commerce_attribute_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->string('name');
            $table->string('color')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_multiselect')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_attribute_sets');
    }
};
