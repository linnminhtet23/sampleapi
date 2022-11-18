<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy_credits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('buy_record_id')->unsigned();
            $table->bigInteger('amount');
            $table->timestamps();
            $table->foreign('buy_record_id')
                ->references('id')->on('buy_records')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buy_credits');
    }
}
