<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListaPreco extends Model
{
    protected $table = 'fiscal_lista_precos';

    protected $fillable = [
		'nome', 'percentual_alteracao'
	];

	public function itens(){
        return $this->hasMany('App\ProdutoListaPreco', 'lista_id', 'id');
    }
}
