<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
			$table->unsignedSmallInteger('date_id');
			$table->unsignedDecimal('value', 4, 2);
			$table->timestamps();

			$table->foreign('date_id')->references('id')->on('dates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('outputs', function (Blueprint $table) {
			$table->dropForeign('outputs_date_id_foreign');
		});

        Schema::dropIfExists('outputs');
    }
}
