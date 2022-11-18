<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy_records', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_name');
            $table->unsignedBigInteger('whole_total');
            $table->unsignedBigInteger('paid');
            $table->unsignedBigInteger('credit');
            $table->bigInteger('shop_id')->unsigned();
            $table->timestamps();
            $table->foreign('shop_id')
                ->references('id')->on('shops')
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
        Schema::dropIfExists('buy_records');
    }
}
