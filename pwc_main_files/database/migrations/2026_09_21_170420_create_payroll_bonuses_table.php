<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_bonuses', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('staff_id', 36);
            $table->string('route_id', 38)->nullable();
            $table->string('month_name');
            $table->integer('year');
            $table->integer('week_number');
            $table->decimal('amount', 10)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_bonuses');
    }
};
