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
            $table->char('id', 36)->primary();
            $table->char('route_id', 36)->nullable()->comment('Staff Route ID');
            $table->char('staff_id', 36)->nullable()->index('deposits_staff_id_foreign')->comment('Staff User ID');
            $table->char('schedule_id', 36)->nullable();
            $table->char('client_payment_id', 38)->nullable();
            $table->string('week');
            $table->string('month');
            $table->integer('year');
            $table->decimal('total_amount', 10)->default(0);
            $table->decimal('deposit_amount', 10)->default(0);
            $table->boolean('is_deposit')->default(false);
            $table->date('deposit_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->enum('payment_type', ['cash', 'zelle'])->nullable()->default('cash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
