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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('quickbooks_customer_id')->nullable()->after('id');
            $table->boolean('quickbooks_synced')->default(false)->after('quickbooks_customer_id');
            $table->timestamp('quickbooks_synced_at')->nullable()->after('quickbooks_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
