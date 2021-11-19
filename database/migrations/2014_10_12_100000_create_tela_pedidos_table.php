<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelaPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tela_pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 30);
            $table->integer('alerta_amarelo')->default(0);
            $table->integer('alerta_vermelho')->default(0);
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
        Schema::dropIfExists('tela_pedidos');
    }
}
