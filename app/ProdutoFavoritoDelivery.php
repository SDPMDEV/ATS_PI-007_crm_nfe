<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProdutoFavoritoDelivery extends Model
{
	protected $fillable = [ 
		'produto_id', 'cliente_id'
	];

	public function cliente(){
		return $this->belongsTo(ClienteDelivery::class, 'cliente_id');
	}

	public function produto(){
		return $this->belongsTo(ProdutoDelivery::class, 'produto_id');
	}
}
