<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBankDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id')->nullable(); // user_id
            $table->integer('route_id')->nullable(); // optional - kis route ka deposit
            $table->date('deposit_date')->nullable(); // kab bank mein deposit kiya
            $table->decimal('deposit_amount', 10, 2)->nullable(); // kitna amount
            $table->string('deposit_slip_number')->nullable(); // bank slip number
            $table->text('notes')->nullable(); // optional notes
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
        Schema::dropIfExists('bank_deposits');
    }
}

