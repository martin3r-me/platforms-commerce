
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_sales')) {
            return;
        }

        Schema::create('commerce_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->foreignId('modules_relations_contact_id')->nullable()->constrained('modules_relations_contacts')->nullOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_sales');
    }
};
