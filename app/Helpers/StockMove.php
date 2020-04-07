<?php

namespace App\Helpers;

use App\Estoque;

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
		$stock = $this->existStock($productId);
		if($stock){ // update
			$stock->quantidade -= $quantity;
			$stock->save();
		}
		
	}
}