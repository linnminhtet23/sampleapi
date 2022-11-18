<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_records', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->unsignedBigInteger('purchase_total');
            $table->unsignedBigInteger('sale_record_total');
            $table->unsignedBigInteger('extra_charges')->default(0);
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
        Schema::dropIfExists('sale_records');
    }
}
