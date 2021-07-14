<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('payment_type_id');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->integer('total');
            $table->decimal('amount', 8, 2);
            $table->decimal('unit_price', 8, 2);
            $table->decimal('discount_price', 8, 2);
            $table->string('transaction_id');
            $table->dateTime('purchased_at');
            $table->integer('status')->comment('1=รอการชำระ,2=ชำระสำเร็จ,3=ชำระไม่สำเร็จ,4=รอการตรวจสอบ,5=ยกเลิก');
            $table->softDeletes();
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
        Schema::dropIfExists('orders');
    }
}
