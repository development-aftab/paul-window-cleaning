<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCmsHomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_homes', function (Blueprint $table) {
            $table->id();
			$table->text('section_one_heading')->nullable();
			$table->text('section_one_description')->nullable();
			$table->text('section_two_heading')->nullable();
			$table->text('two_sub_section_one_heading')->nullable();
			$table->text('two_sub_section_one_title')->nullable();
			$table->text('two_sub_section_two_heading')->nullable();
			$table->text('two_sub_section_two_title')->nullable();
			$table->text('section_three_heading')->nullable();
			$table->text('section_three_description')->nullable();
			$table->text('three_sub_section_one_heading')->nullable();
			$table->text('three_sub_section_one_description')->nullable();
			$table->text('three_sub_section_one_link')->nullable();
			$table->text('section_two_image_one')->nullable();
			$table->text('section_two_image_two')->nullable();
			$table->text('two_sub_section_one_icon')->nullable();
			$table->text('two_sub_section_two_icon')->nullable();
			$table->text('three_sub_section_one_image')->nullable();
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
        Schema::dropIfExists('cms_homes');
    }
}
