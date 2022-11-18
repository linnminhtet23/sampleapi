<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleBuysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('single_buys', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('buy_record_id')->unsigned();
            $table->bigInteger('item_id')->unsigned();
            $table->bigInteger('price');
            $table->integer('quantity');
            $table->bigInteger('subtotal');
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
        Schema::dropIfExists('single_buys');
    }
}
