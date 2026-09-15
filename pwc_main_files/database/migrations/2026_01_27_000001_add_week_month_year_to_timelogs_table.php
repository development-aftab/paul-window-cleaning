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
        Schema::table('timelogs', function (Blueprint $table) {
            $table->string('week')->nullable()->after('service_date')->comment('Week number: week0, week1, week2, week3');
            $table->string('month')->nullable()->after('week')->comment('Month name: January, February, etc.');
            $table->integer('year')->nullable()->after('month')->comment('Year: 2025, 2026, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timelogs', function (Blueprint $table) {
            $table->dropColumn(['week', 'month', 'year']);
        });
    }
};

