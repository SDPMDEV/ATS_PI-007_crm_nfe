<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ItemPurchase;

class Estoque extends Model
{
	protected $fillable = [
        'produto_id', 'quantidade', 'valor_compra', 'validade'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function value_purchase($productId = null){

    	$item = ItemPurchase::
    	where('produto_id', $productId)
    	->orderBy('id', 'desc')
    	->first();
    	return $item != null ? $item->value : 0;
    }

}
