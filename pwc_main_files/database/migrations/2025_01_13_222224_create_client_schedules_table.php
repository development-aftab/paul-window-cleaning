<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateClientSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_schedules', function (Blueprint $table) {
            $table->id();
			$table->integer('client_id')->nullable();
			$table->string('month')->nullable();
			$table->string('week')->nullable();
			$table->string('start_date')->nullable();
			$table->string('end_date')->nullable();
			$table->string('payment_type')->nullable();
			$table->text('note')->nullable();
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
        Schema::dropIfExists('client_schedules');
    }
}
