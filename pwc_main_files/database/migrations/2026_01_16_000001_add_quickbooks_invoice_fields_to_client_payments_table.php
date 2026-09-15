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
            $table->string('quickbooks_invoice_id')->nullable()->after('final_price');
            $table->boolean('quickbooks_synced')->default(false)->after('quickbooks_invoice_id');
            $table->timestamp('quickbooks_synced_at')->nullable()->after('quickbooks_synced');
            $table->string('quickbooks_invoice_number')->nullable()->after('quickbooks_synced_at');
            // $table->string('payment_status')->default('pending')->after('quickbooks_invoice_number'); // pending, paid, cancelled
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_payments', function (Blueprint $table) {
            $table->dropColumn([
                'quickbooks_invoice_id',
                'quickbooks_synced',
                'quickbooks_synced_at',
                'quickbooks_invoice_number',
                'payment_status'
            ]);
        });
    }
};

