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
        Schema::create('assign_weeks', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('client_id', 36)->nullable();
            $table->string('assign_week')->nullable();
            $table->string('week')->nullable();
            $table->text('note')->nullable();
            $table->string('cost')->nullable();
            $table->string('price_type')->nullable();
            $table->string('payment_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_weeks');
    }
};
