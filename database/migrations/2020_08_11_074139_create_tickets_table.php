<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title_th');
            $table->string('title_en');
            $table->text('description_th')->nullable();
            $table->text('description_en')->nullable();
            $table->text('condition_th')->nullable();
            $table->text('condition_en')->nullable();
            // $table->integer('quantity')->default(0);
            $table->decimal('price', 8, 2)->default(0.00);
            $table->decimal('discount_price', 8, 2)->default(0.00);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->tinyInteger('active');
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('tickets');
    }
}
