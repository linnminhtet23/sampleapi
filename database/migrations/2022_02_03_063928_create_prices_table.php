<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('item_id')->unsigned();
            $table->bigInteger('region_id')->unsigned();
            $table->unsignedBigInteger('sale_price');
            $table->bigInteger('shop_id')->unsigned();
            $table->timestamps();
            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onDelete('cascade');
            $table->foreign('region_id')
                ->references('id')->on('regions')
                ->onDelete('cascade');
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
        Schema::dropIfExists('prices');
    }
}
