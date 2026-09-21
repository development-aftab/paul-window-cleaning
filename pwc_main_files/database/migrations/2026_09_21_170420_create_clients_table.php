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
        Schema::create('clients', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('quickbooks_customer_id')->nullable();
            $table->boolean('quickbooks_synced')->default(false);
            $table->timestamp('quickbooks_synced_at')->nullable();
            $table->char('user_id', 36)->nullable();
            $table->string('parent_id')->nullable();
            $table->char('staff_id', 36)->nullable();
            $table->string('name', 225)->nullable();
            $table->string('client_type')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('commission_percentage', 225)->nullable();
            $table->string('service_frequency')->nullable();
            $table->string('start_date')->nullable();
            $table->string('second_start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('start_hour')->nullable();
            $table->string('end_hour')->nullable();
            $table->string('price_type')->nullable();
            $table->string('cost')->nullable();
            $table->text('description')->nullable();
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();
            $table->text('additional_note')->nullable();
            $table->string('schedule')->nullable()->default('unassigned');
            $table->string('house_no', 225)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 225)->nullable();
            $table->string('state', 225)->nullable();
            $table->string('postal', 225)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('status', 191)->nullable();
            $table->string('contact_name', 225)->nullable();
            $table->string('contact_email', 225)->nullable();
            $table->string('contact_phone', 225)->nullable();
            $table->boolean('is_child')->nullable()->default(false);
            $table->string('position', 191)->nullable();
            $table->string('staff_position')->nullable()->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
