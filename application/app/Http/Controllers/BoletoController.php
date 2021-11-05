<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Boleto\Banco\Bb;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
class BoletoController extends Controller
{
	public function index(){
		$beneficiario = new Pessoa([
			'documento' => '00.000.000/0000-00',
			'nome'      => 'Company co.',
			'cep'       => '00000-000',
			'endereco'  => 'Street name, 123',
			'bairro' => 'district',
			'uf'        => 'UF',
			'cidade'    => 'City',
		]);

		$pagador = new Pessoa([
			'documento' => '00.000.000/0000-00',
			'nome'      => 'Company co.',
			'cep'       => '00000-000',
			'endereco'  => 'Street name, 123',
			'bairro' => 'district',
			'uf'        => 'UF',
			'cidade'    => 'City',
		]);

		$boleto = new Bb(
			[
				'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '001.png',
				'dataVencimento'         => new \Carbon\Carbon(),
				'valor'                  => 100,
				'multa'                  => false,
				'juros'                  => false,
				'numero'                 => 1,
				'numeroDocumento'        => 1,
				'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
				'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
				'aceite'                 => 'S',
				'especieDoc'             => 'DM',
				'pagador'                => $pagador,
				'beneficiario'           => $beneficiario,
				'carteira'               => 1111,
				'carteira'               => 11,
				'convenio'               => 1234567,
			]
		);
	}
}
