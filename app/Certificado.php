<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $table = 'fiscal_certificados';

    protected $fillable = [
		'senha', 'arquivo'
	];
}
