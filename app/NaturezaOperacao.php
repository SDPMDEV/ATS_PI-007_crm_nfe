<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NaturezaOperacao extends Model
{
    protected $table = 'fiscal_natureza_operacaos';

    protected $fillable = [
		'natureza', 'CFOP_entrada_estadual', 'CFOP_entrada_inter_estadual',
		'CFOP_saida_estadual', 'CFOP_saida_inter_estadual', 'business_id'
	];
}
