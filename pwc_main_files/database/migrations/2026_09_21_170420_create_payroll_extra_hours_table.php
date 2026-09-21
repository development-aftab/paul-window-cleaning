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
        Schema::create('payroll_extra_hours', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('staff_id', 36);
            $table->char('route_id', 36)->index();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('per_hour_amount', 10)->default(0);
            $table->decimal('total_extra_hours', 10)->default(0);
            $table->decimal('total_extra_amount', 10)->default(0);
            $table->char('created_by', 36)->index('payroll_extra_hours_created_by_foreign');
            $table->timestamps();

            $table->unique(['staff_id', 'route_id', 'period_start', 'period_end'], 'payroll_extra_hours_staff_route_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_extra_hours');
    }
};
