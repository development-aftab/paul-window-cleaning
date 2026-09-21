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
        Schema::create('cms_homes', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->longText('section_one_heading')->nullable();
            $table->longText('section_one_description')->nullable();
            $table->longText('section_two_heading')->nullable();
            $table->longText('two_sub_section_one_heading')->nullable();
            $table->longText('two_sub_section_one_title')->nullable();
            $table->longText('two_sub_section_two_heading')->nullable();
            $table->longText('two_sub_section_two_title')->nullable();
            $table->longText('section_three_heading')->nullable();
            $table->longText('section_three_description')->nullable();
            $table->longText('three_sub_section_one_heading')->nullable();
            $table->longText('three_sub_section_one_description')->nullable();
            $table->longText('three_sub_section_one_link')->nullable();
            $table->longText('section_two_image_one')->nullable();
            $table->longText('section_two_image_two')->nullable();
            $table->longText('two_sub_section_one_icon')->nullable();
            $table->longText('two_sub_section_two_icon')->nullable();
            $table->longText('three_sub_section_one_image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_homes');
    }
};
