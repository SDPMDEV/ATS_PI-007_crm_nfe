<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('comanda', 12);
            $table->string('observacao', 200);
            $table->boolean('status');

            $table->integer('bairro_id')->nullable()->unsigned();
            $table->foreign('bairro_id')->references('id')->on('bairro_deliveries');

            $table->string('nome', 50);
            $table->string('rua', 50);
            $table->string('numero', 10);

            $table->string('referencia', 30);
            $table->string('telefone', 15);

            $table->boolean('desativado');
            $table->timestamp('data_registro')->useCurrent();
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
        Schema::dropIfExists('pedidos');
    }
}
