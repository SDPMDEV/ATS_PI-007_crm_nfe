<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComissaoVenda extends Model
{
    protected $fillable = [
		'funcionario_id', 'venda_id', 'tabela', 'valor', 'status'
	];

	public function funcionario(){
		return $this->belongsTo(Funcionario::class, 'funcionario_id');
	}
	public function tipo(){
		if($this->tabela == 'vendas'){
			return 'Venda';
		}else{
			return 'PDV';
		}
	}
}
