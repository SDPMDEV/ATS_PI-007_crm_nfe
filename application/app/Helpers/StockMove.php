<?php

namespace App\Helpers;

use App\Estoque;
use App\Produto;

class StockMove {
	private function existStock($productId){
		$p = Estoque
		::where('produto_id', $productId)
		->first();
		return $p != null ? $p : 0;
	}

	public function getStockProduct($productId){
		$stock = $this->existStock($productId);
		return $stock->quantity ?? 0;
	}

	public function pluStock($productId, $quantity, $value = -1){

		$produto = Produto::find($productId);
		$quantity = (float)$quantity * $produto->conversao_unitaria;
		$stock = $this->existStock($productId);
		if($stock){ // update
			$stock->quantidade += $quantity;
			$stock->valor_compra = $value > -1 ? $value : $stock->valor_compra;
		}else{
			$stock = new Estoque();
			$stock->valor_compra = $value;
			$stock->quantidade = $quantity;
			$stock->produto_id = $productId;
		}
		return $stock->save();
	}

	public function downStock($productId, $quantity){
		$produto = Produto::find($productId);
		$quantity = (float)$quantity * $produto->conversao_unitaria;
		$stock = $this->existStock($productId);
		if($stock){ // update
			$stock->quantidade -= $quantity;
			if($stock->quantidade < 0.010 ) $stock->quantidade = 0;
			$stock->save();
		}
		
	}
}