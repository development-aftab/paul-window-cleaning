<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCmsAboutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_abouts', function (Blueprint $table) {
            $table->id();
			$table->string('section_one_heading')->nullable();
			$table->string('section_one_description')->nullable();
			$table->string('section_two_heading')->nullable();
			$table->string('two_sub_section_one_heading')->nullable();
			$table->string('two_sub_section_one_description')->nullable();
			$table->string('two_sub_section_one_link_one')->nullable();
			$table->string('two_sub_section_one_link_two')->nullable();
			$table->string('two_sub_section_two_heading')->nullable();
			$table->string('two_sub_section_two_description')->nullable();
			$table->string('two_sub_section_two_link_one')->nullable();
			$table->string('two_sub_section_two_link_two')->nullable();
			$table->string('two_sub_section_three_heading')->nullable();
			$table->string('two_sub_section_three_description')->nullable();
			$table->string('two_sub_section_three_link_one')->nullable();
			$table->string('two_sub_section_three_link_two')->nullable();
			$table->string('two_sub_section_four_heading')->nullable();
			$table->string('two_sub_section_four_description')->nullable();
			$table->string('two_sub_section_four_link_one')->nullable();
			$table->string('two_sub_section_four_link_two')->nullable();
			$table->string('two_sub_section_five_heading')->nullable();
			$table->string('two_sub_section_five_description')->nullable();
			$table->string('two_sub_section_five_link_one')->nullable();
			$table->string('two_sub_section_five_link_two')->nullable();
			$table->string('section_one_image')->nullable();
			$table->string('two_sub_section_one_image')->nullable();
			$table->string('two_sub_section_two_image')->nullable();
			$table->string('two_sub_section_three_image')->nullable();
			$table->string('two_sub_section_four_image')->nullable();
			$table->string('two_sub_section_five_image')->nullable();
			$table->string('two_sub_section_one_title')->nullable();
			$table->string('two_sub_section_two_title')->nullable();
			$table->string('two_sub_section_three_title')->nullable();
			$table->string('two_sub_section_four_title')->nullable();
			$table->string('two_sub_section_five_title')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_abouts');
    }
}
