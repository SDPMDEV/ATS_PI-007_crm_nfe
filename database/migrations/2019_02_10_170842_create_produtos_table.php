<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nome', 100);
            $table->string('cor', 20);
            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')->on('categorias')
            ->onDelete('cascade');
            $table->decimal('valor_venda', 10,2)->default(0);
            $table->string('NCM', 13)->default("");
            $table->string('codBarras', 13)->default("");
            $table->string('CEST', 10)->default("");
            $table->string('CST_CSOSN', 3)->default("");
            $table->string('CST_PIS', 3)->default("");
            $table->string('CST_COFINS', 3)->default("");
            $table->string('CST_IPI', 3)->default("");
           
            $table->string('unidade_compra', 10);
            $table->string('conversao_unitaria')->default(1);
            $table->string('unidade_venda', 10);
            $table->boolean('composto')->default(false);
            $table->boolean('valor_livre');

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
        Schema::dropIfExists('produtos');
    }
}
