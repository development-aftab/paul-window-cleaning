<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateClientPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
			$table->string('client_id')->nullable();
			$table->string('option')->nullable();
			$table->string('option_two')->nullable();
			$table->string('option_three')->nullable();
			$table->string('option_four')->nullable();
			$table->string('reason')->nullable();
			$table->string('scope')->nullable();
			$table->string('amount')->nullable();
			$table->string('price_charge_one')->nullable();
			$table->string('price_charge_two')->nullable();
			$table->string('final_price')->nullable();
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
        Schema::dropIfExists('client_payments');
    }
}
