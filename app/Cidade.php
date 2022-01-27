<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    protected $table = 'fiscal_cidades';

    public static function getCidadeCod($codMun){
    	return Cidade::
    	where('codigo', $codMun)
    	->first();
	}

	public static function getId($id){
    	return Cidade::
    	where('id', $id)
    	->first();
	}
}
