<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Analytics page also creates this table on first use, so guard it.
        if (Schema::hasTable('analytics_history')) {
            return;
        }

        Schema::create('analytics_history', function (Blueprint $table) {
            $table->id();
            $table->date('service_date')->index();
            $table->unsignedTinyInteger('week_number')->nullable();   // week 1-4 of the 4-week cycle
            $table->string('route_name');
            $table->string('route_key')->index();                     // lower-cased route name for matching
            $table->string('account_name')->nullable();
            $table->string('client_type', 40)->default('commercial')->index();
            $table->decimal('gross_sales', 12, 2)->default(0);
            $table->decimal('hours', 8, 2)->default(0);
            $table->string('import_batch', 60)->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_history');
    }
};
