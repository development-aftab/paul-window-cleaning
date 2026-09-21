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
        Schema::create('profiles', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('user_id', 36)->nullable()->index('profiles_user_id_foreign');
            $table->char('client_id', 36)->nullable();
            $table->text('bio')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('pic')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 191)->nullable();
            $table->enum('employment_type', ['employee', 'subcontractor'])->nullable()->default('employee');
            $table->string('postal')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('street_number', 191)->nullable();
            $table->string('zip_code', 191)->nullable();
            $table->string('hiring_date', 191)->nullable();
            $table->decimal('training_rate', 10)->nullable();
            $table->decimal('normal_rate', 10)->nullable();
            $table->longText('additional_emails')->nullable();
            $table->longText('additional_phones')->nullable();
            $table->longText('additional_names')->nullable();
            $table->longText('additional_positions')->nullable();
            $table->longText('additional_notes')->nullable();
            $table->string('invoice_email')->nullable();
            $table->string('plain_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
