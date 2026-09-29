<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PREPAID-SERVICES: simple client wallet (one row per pre-paid service)
return new class extends Migration
{
    public function up(): void
    {
        // The app also creates this table on first use, so guard it.
        if (Schema::hasTable('client_prepaid_services')) {
            return;
        }

        Schema::create('client_prepaid_services', function (Blueprint $table) {
            $table->char('id', 36)->primary();                        // UUID, like every other table in this app
            $table->char('client_id', 36)->index();
            $table->char('payment_id', 36)->index();                 // client_payments.id where the extra was collected
            $table->char('schedule_id', 36)->nullable();              // visit where the extra was collected
            $table->char('staff_id', 36)->nullable()->index();        // staff who collected it
            $table->unsignedSmallInteger('slot_number')->default(1);  // 1..N of "Paid extra for N dates"
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status', 20)->default('pending')->index(); // pending | used
            $table->timestamp('used_at')->nullable();
            $table->char('used_by', 36)->nullable();
            $table->char('used_payment_id', 36)->nullable();          // the unpaid visit this pre-paid service settled ("Use Pre-Paid")
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_prepaid_services');
    }
};
