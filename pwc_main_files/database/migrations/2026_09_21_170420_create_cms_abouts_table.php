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
        Schema::create('cms_abouts', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->longText('section_one_heading')->nullable();
            $table->longText('section_one_description')->nullable();
            $table->longText('section_two_heading')->nullable();
            $table->longText('two_sub_section_one_heading')->nullable();
            $table->longText('two_sub_section_one_description')->nullable();
            $table->longText('two_sub_section_one_link_one')->nullable();
            $table->longText('two_sub_section_one_link_two')->nullable();
            $table->longText('two_sub_section_two_heading')->nullable();
            $table->longText('two_sub_section_two_description')->nullable();
            $table->longText('two_sub_section_two_link_one')->nullable();
            $table->longText('two_sub_section_two_link_two')->nullable();
            $table->longText('two_sub_section_three_heading')->nullable();
            $table->longText('two_sub_section_three_description')->nullable();
            $table->longText('two_sub_section_three_link_one')->nullable();
            $table->longText('two_sub_section_three_link_two')->nullable();
            $table->longText('two_sub_section_four_heading')->nullable();
            $table->longText('two_sub_section_four_description')->nullable();
            $table->longText('two_sub_section_four_link_one')->nullable();
            $table->longText('two_sub_section_four_link_two')->nullable();
            $table->longText('two_sub_section_five_heading')->nullable();
            $table->longText('two_sub_section_five_description')->nullable();
            $table->longText('two_sub_section_five_link_one')->nullable();
            $table->longText('two_sub_section_five_link_two')->nullable();
            $table->longText('section_one_image')->nullable();
            $table->longText('two_sub_section_one_image')->nullable();
            $table->longText('two_sub_section_two_image')->nullable();
            $table->longText('two_sub_section_three_image')->nullable();
            $table->longText('two_sub_section_four_image')->nullable();
            $table->longText('two_sub_section_five_image')->nullable();
            $table->longText('two_sub_section_one_title')->nullable();
            $table->longText('two_sub_section_two_title')->nullable();
            $table->longText('two_sub_section_three_title')->nullable();
            $table->longText('two_sub_section_four_title')->nullable();
            $table->longText('two_sub_section_five_title')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_abouts');
    }
};
