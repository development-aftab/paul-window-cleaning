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
        Schema::create('client_price_lists', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('client_id', 36)->nullable()->index('idx_cpl_client');
            $table->string('name')->nullable();
            $table->integer('position')->nullable()->default(0);
            $table->string('value')->nullable()->default('0');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_price_lists');
    }
};
