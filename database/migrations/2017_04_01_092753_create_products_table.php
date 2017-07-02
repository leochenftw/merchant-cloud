<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('Title', 128);
            $table->string('Barcode', 128);
    		$table->string('Measurement', 8)->nullable();
            $table->float('Width', 8, 2)->nullable();
            $table->float('Height', 8, 2)->nullable();
            $table->float('Depth', 8, 2)->nullable();
    		$table->float('Weight', 8, 2)->nullable();
    		$table->integer('StockCount')->nullable();
            $table->integer('supplier_id')->unsigned()->nullable();
            $table->integer('manufacturer_id')->unsigned()->nullable();
            // $table->integer('translation_id')->unsigned()->nullable();


            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('cascade');
            // $table->foreign('translation_id')->references('id')->on('translations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
