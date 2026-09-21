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
        Schema::create('timelogs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('route_id', 36)->nullable()->index('timelogs_route_id_foreign');
            $table->char('staff_id', 36)->nullable()->index('timelogs_staff_id_foreign');
            $table->date('service_date')->nullable();
            $table->string('week')->nullable()->comment('Week number: week0, week1, week2, week3');
            $table->string('month')->nullable()->comment('Month name: January, February, etc.');
            $table->integer('year')->nullable()->comment('Year: 2025, 2026, etc.');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('total_hours')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timelogs');
    }
};
