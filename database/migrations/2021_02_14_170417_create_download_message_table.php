<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('download_message', function (Blueprint $table) {
            $table->id();
			$table->string('sl')->nullable();
			$table->string('mobile_number');
			$table->string('message_text');
			$table->string('msg_date');
			$table->string('download_flag')->nullable();
			$table->string('parse_flag')->nullable();
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
        Schema::dropIfExists('download_message');
    }
}
