<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('powers', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('date_id');
            $table->unsignedTinyInteger('time_id');
            $table->unsignedSmallInteger('value');
            $table->timestamps();

            $table->foreign('date_id')->references('id')->on('dates');
            $table->foreign('time_id')->references('id')->on('times');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('powers', function (Blueprint $table) {
			$table->dropForeign('powers_time_id_foreign');
			$table->dropForeign('powers_date_id_foreign');
		});

        Schema::dropIfExists('powers');
    }
}
