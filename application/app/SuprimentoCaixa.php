<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuprimentoCaixa extends Model
{
    protected $fillable = [
        'usuario_id', 'valor', 'observacao'
    ];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
