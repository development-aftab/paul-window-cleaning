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
        Schema::create('deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('route_id', 36)->nullable()->comment('Staff Route ID');
            $table->string('week'); // e.g., "week1", "week2"
            $table->string('month'); // e.g., "January"
            $table->integer('year'); // e.g., 2026
            $table->decimal('total_amount', 10, 2)->default(0); // Total cash collected
            $table->decimal('deposit_amount', 10, 2)->default(0); // Amount deposited
            $table->boolean('is_deposit')->default(false); // Deposit complete or not
            $table->date('deposit_date')->nullable(); // When deposit was made
            $table->text('notes')->nullable(); // Optional notes
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
