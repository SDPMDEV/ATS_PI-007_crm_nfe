<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EscritorioContabil extends Model
{
    protected $table = 'fiscal_escritorio_contabils';

    protected $fillable = [
        'razao_social', 'nome_fantasia', 'cnpj', 'ie', 'logradouro',
        'numero', 'bairro', 'fone', 'email', 'cep'
    ];
}
