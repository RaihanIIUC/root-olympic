<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherHeadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_head', function (Blueprint $table) {
            $table->id();
			$table->string('sl');
			$table->string('type');
			$table->string('msg_date');
			$table->string('od_date');
			$table->string('mobile_number');
			$table->string('route');
			$table->string('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_head');
    }
}
