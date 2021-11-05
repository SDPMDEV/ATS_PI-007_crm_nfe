<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelaPedido extends Model
{
    protected $fillable = [
        'nome', 'alerta_amarelo', 'alerta_vermelho'
    ];
}
