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
        Schema::create('staff_requirements', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('staff_id', 36)->nullable();
            $table->string('name')->nullable();
            $table->string('quantity')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->string('timestamp')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_requirements');
    }
};
