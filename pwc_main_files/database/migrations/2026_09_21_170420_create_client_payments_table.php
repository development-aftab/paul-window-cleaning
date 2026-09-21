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
        Schema::create('client_payments', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('client_id', 36)->nullable();
            $table->char('staff_id', 36)->nullable();
            $table->char('schedule_id', 36)->nullable()->index('idx_cp_schedule');
            $table->string('option')->nullable();
            $table->string('option_two')->nullable();
            $table->string('option_three')->nullable();
            $table->string('option_four')->nullable();
            $table->string('option_five')->nullable();
            $table->string('partial_completed_scope', 225)->nullable();
            $table->string('reason')->nullable();
            $table->string('scope')->nullable();
            $table->string('amount')->nullable();
            $table->string('price_charge_one')->nullable();
            $table->string('price_charge_two')->nullable();
            $table->string('final_price')->nullable();
            $table->string('payment_method')->nullable()->comment('Payment method used (Cash, Check, Credit Card, etc.)');
            $table->string('payment_type')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('status')->default('pending')->comment('pending, paid, cancelled');
            $table->date('payment_date')->nullable()->comment('Date when payment was received');
            $table->boolean('edited_after_submission')->nullable()->default(false);
            $table->string('quickbooks_invoice_id')->nullable();
            $table->boolean('quickbooks_synced')->default(false);
            $table->timestamp('quickbooks_synced_at')->nullable();
            $table->string('quickbooks_invoice_number')->nullable();
            $table->string('quickbooks_payment_id')->nullable()->comment('QuickBooks Payment ID');
            $table->string('day_number')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('route_name')->nullable();
            $table->char('route_id_display', 36)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_payments');
    }
};
