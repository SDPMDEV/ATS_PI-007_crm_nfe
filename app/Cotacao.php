<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    protected $fillable = [
        'forma_pagamento', 'fornecedor_id', 'valor', 'referencia', 
        'resposta', 'ativa', 'observacao', 'link', 'desconto', 'responsavel'
    ];

    public function fornecedor(){
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function itens(){
        return $this->hasMany('App\ItemCotacao', 'cotacao_id', 'id');
    }

}
