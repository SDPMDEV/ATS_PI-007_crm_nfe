<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPedidoDelivery extends Model
{
    protected $fillable = [
		'pedido_id', 'produto_id', 'status', 'quantidade', 'observacao', 'tamanho_id'
	];

	public function produto(){
        return $this->belongsTo(ProdutoDelivery::class, 'produto_id');
    }

    public function tamanho(){
        return $this->belongsTo(TamanhoPizza::class, 'tamanho_id');
    }

    public function itensAdicionais(){
        return $this->hasMany('App\ItemPedidoComplementoDelivery', 'item_pedido_id', 'id');
    }

    public function pedido(){
        return $this->belongsTo(PedidoDelivery::class, 'pedido_id');
    }

    public function sabores(){
        return $this->hasMany('App\ItemPizzaPedido', 'item_pedido', 'id');
    }
}
