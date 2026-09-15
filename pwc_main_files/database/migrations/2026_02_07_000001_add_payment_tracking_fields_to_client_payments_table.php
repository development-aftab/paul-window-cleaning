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
        Schema::table('client_payments', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('final_price')->comment('pending, paid, cancelled');
            $table->date('payment_date')->nullable()->after('status')->comment('Date when payment was received');
            $table->string('quickbooks_payment_id')->nullable()->after('quickbooks_invoice_number')->comment('QuickBooks Payment ID');
            $table->string('payment_method')->nullable()->after('quickbooks_payment_id')->comment('Payment method used (Cash, Check, Credit Card, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_payments', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'payment_date',
                'quickbooks_payment_id',
                'payment_method'
            ]);
        });
    }
};

