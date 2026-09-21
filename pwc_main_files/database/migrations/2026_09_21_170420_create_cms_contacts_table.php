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
        Schema::create('cms_contacts', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->longText('section_one_heading')->nullable();
            $table->longText('section_one_description')->nullable();
            $table->longText('section_one_icon')->nullable();
            $table->longText('section_two_heading')->nullable();
            $table->longText('section_two_phone')->nullable();
            $table->longText('section_two_icon')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_contacts');
    }
};
