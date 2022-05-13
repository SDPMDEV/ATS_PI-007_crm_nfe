<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    public $timestamps = false;
    
    protected $table = 'sma_companies';

    protected $fillable = [
        'group_id',
        'group_name',
        'name',
        'company',
        'ie_rg',
        'cpf_cnpj',
        'rua',
        'city',
        'state',
        'cep',
        'country',
        'phone',
        'email',
        'logo'
    ];

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public static function verificaCadastrado($cnpj){
    	
    	$forn = Fornecedor::where('cpf_cnpj', $cnpj)
    	->first();

    	return $forn;

    }
}
