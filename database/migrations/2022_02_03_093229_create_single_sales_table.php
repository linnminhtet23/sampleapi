<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSingleSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('single_sales', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sale_record_id')->unsigned();
            $table->bigInteger('item_id')->unsigned();
            $table->bigInteger('price');
            $table->integer('quantity');
            $table->bigInteger('subtotal');
            $table->timestamps();
            $table->foreign('sale_record_id')
                ->references('id')->on('sale_records')
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
        Schema::dropIfExists('single_sales');
    }
}
