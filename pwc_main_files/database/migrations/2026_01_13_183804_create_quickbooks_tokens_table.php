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
        Schema::create('quickbooks_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->string('realm_id'); // Company ID
            $table->timestamp('expires_at'); // When access token expires
            $table->boolean('is_active')->default(true); // Only one active token at a time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quickbooks_tokens');
    }
};
