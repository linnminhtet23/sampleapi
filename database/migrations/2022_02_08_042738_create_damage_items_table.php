<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDamageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('damage_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('single_buy_id')->unsigned();
            $table->boolean('status')->default(0);
            $table->bigInteger('shop_id')->unsigned();
            $table->timestamps();
            $table->foreign('single_buy_id')
                ->references('id')->on('single_buys')
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
        Schema::dropIfExists('damage_items');
    }
}
