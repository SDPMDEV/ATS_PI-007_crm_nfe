<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
	protected $fillable = [
		'fornecedor_id', 'usuario_id', 'nf', 'desconto', 'valor', 'observacao', 'xml_path',
		'chave', 'estado'
	];

	public function fornecedor(){
		return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
	}

	public function usuario(){
		return $this->belongsTo(Usuario::class, 'usuario_id');
	}

	public function itens(){
        return $this->hasMany('App\ItemCompra', 'compra_id', 'id');
    }

    public function somaItems(){
		if(count($this->itens) > 0){
			$total = 0;
			foreach($this->itens as $t){
				$total += $t->quantidade * $t->valor_unitario;
			}
			return $total;
		}else{
			return 0;
		}
	}

	public static function filtroData($dataInicial, $dataFinal){
		$c = Compra::
		select('compras.*')
		->whereBetween('date_register', [$dataInicial, 
			$dataFinal]);

		return $c->get();
	}
	
	public static function filtroDataFornecedor($fornecedor, $dataInicial, $dataFinal){
		$c = Compra::
		select('compras.*')
		->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id')
		->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%")
		->whereBetween('date_register', [$dataInicial, 
			$dataFinal]);

		return $c->get();
	}

	public static function filtroFornecedor($fornecedor){
		$c = Compra::
		select('compras.*')
		->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id')
		->where('razao_social', 'LIKE', "%$fornecedor%");

		return $c->get();
	}


	public static function pesquisaProduto($pesquisa){
		return Compra::
		select('compras.*')
		->join('item_compras', 'compras.id' , '=', 'item_compras.compra_id')
		->join('produtos', 'produtos.id' , '=', 'item_compras.produto_id')
		->where('produtos.nome', 'LIKE', "%$pesquisa%")
		->get();
	}

}
