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
        Schema::table('payroll_extra_hours', function (Blueprint $table) {
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['route_id'])->references(['id'])->on('staff_routes')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['staff_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_extra_hours', function (Blueprint $table) {
            $table->dropForeign('payroll_extra_hours_created_by_foreign');
            $table->dropForeign('payroll_extra_hours_route_id_foreign');
            $table->dropForeign('payroll_extra_hours_staff_id_foreign');
        });
    }
};
