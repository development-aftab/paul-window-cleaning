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
        Schema::create('staff_log_hours', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('staff_id');
            $table->string('route_id');
            $table->unsignedTinyInteger('week_number');
            $table->date('week_start_date');
            $table->date('service_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('duration_hours');
            $table->timestamps();
            $table->decimal('rate_amount', 10)->nullable();
            $table->enum('rate_type', ['normal', 'training'])->nullable();

            $table->index(['staff_id', 'route_id', 'week_start_date'], 'staff_extra_hours_staff_id_route_id_week_start_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_log_hours');
    }
};
