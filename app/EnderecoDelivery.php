<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnderecoDelivery extends Model
{
	protected $fillable = [
		'cliente_id', 'rua', 'numero', 'bairro', 'referencia', 'latitude', 'longitude'
	];

	public function produto(){
        return $this->belongsTo(ClienteDelivery::class, 'cliente_id');
    }
}
