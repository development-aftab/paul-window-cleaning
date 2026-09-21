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
            $table->string('id', 36)->primary();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->string('realm_id')->index('idx_realm_id');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true)->index('idx_is_active');
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
