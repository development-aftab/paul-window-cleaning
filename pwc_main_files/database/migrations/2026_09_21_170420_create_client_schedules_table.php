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
        Schema::create('client_schedules', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('staff_id', 36)->nullable();
            $table->date('service_date')->nullable();
            $table->boolean('is_increase')->default(false);
            $table->char('client_id', 36)->nullable();
            $table->string('month')->nullable();
            $table->string('week_month', 225)->nullable();
            $table->string('week')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('payment_type')->nullable();
            $table->text('note')->nullable();
            $table->text('note_two')->nullable();
            $table->string('note_type', 225)->nullable();
            $table->string('note_date', 225)->nullable();
            $table->integer('note_week_no')->default(0);
            $table->string('extra_work_price', 225)->nullable();
            $table->text('extra_work')->nullable();
            $table->string('extra_work_price_id', 225)->nullable();
            $table->string('status')->nullable()->default('pending')->index('idx_cs_status');
            $table->timestamp('submitted_at')->nullable();
            $table->string('position')->nullable();
            $table->boolean('priority')->nullable()->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'start_date'], 'idx_cs_client_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_schedules');
    }
};
