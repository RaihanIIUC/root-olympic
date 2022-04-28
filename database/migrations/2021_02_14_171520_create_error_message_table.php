<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_message', function (Blueprint $table) {
            $table->id();
			$table->string('sl')->nullable();
			$table->string('msg_date');
			$table->string('mobile_number');
			$table->string('sms_text');
			$table->string('error_report');
			$table->integer('status');
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
        Schema::dropIfExists('error_message');
    }
}
