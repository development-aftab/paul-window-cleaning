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
        Schema::create('client_routes', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('client_id', 36)->nullable()->index('idx_cr_client');
            $table->char('route_id', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_routes');
    }
};
