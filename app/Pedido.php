<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
	protected $fillable = [
		'comanda', 'status', 'desativado', 'observacao'
	];

	public function itens(){
		return $this->hasMany('App\ItemPedido', 'pedido_id', 'id');
	}

	public function somaItems(){
		if(count($this->itens) > 0){
			$total = 0;
			foreach($this->itens as $i){
				$valorItem = 0;
				$i->produto;

				// if(count($i->sabores) > 0){
				// 	$maiorValor = 0;
				// 	foreach($i->sabores as $s){
				// 		$v = $s->maiorValor($s->sabor_id, $i->tamanho_pizza_id);
				// 		if($v > $maiorValor) $maiorValor = $v;

				// 		$s->produto->produto;
				// 	}
				// 	$valorItem = $maiorValor;
				// }else if(isset($i->produto->produto) && $i->produto->produto->valor_venda > 0){
				// 	$valorItem = $i->produto->produto->valor_venda;
				// }else{
				// 	$valorItem = $i->produto->valor_venda;
				// }
				foreach($i->sabores as $s){$s->produto->produto;}
				$valorItem = $i->valor;

				$somaadicionais = 0;
				if(count($i->itensAdicionais) > 0){
					foreach($i->itensAdicionais as $a){
						$somaadicionais += $a->adicional->valor * $i->quantidade;
					}
				}

				$total += $somaadicionais;
				$i->valorItem = $valorItem + $somaadicionais;
				$total += $i->quantidade * $valorItem;
			}
			return $total;
		}else{
			return 0;
		}
	}

	public function itensPendentes(){
		$cont = 0;
		foreach($this->itens as $i){
			if(!$i->status) $cont++;
		}
		return $cont;
	}
	
}
