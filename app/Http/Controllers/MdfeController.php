<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Mdfe;
use App\MunicipioCarregamento;
use App\Percurso;
use App\Ciot;
use App\ValePedagio;
use App\InfoDescarga;
use App\NFeDescarga;
use App\CTeDescarga;
use App\UnidadeCarga;
use App\LacreTransporte;
use App\LacreUnidadeCarga;

use App\Veiculo;
use App\ConfigNota;

class MdfeController extends Controller
{

	public function __construct(){
		$this->middleware(function ($request, $next) {
			$value = session('user_logged');
			if(!$value){
				return redirect("/login");
			}else{
				if($value['acesso_fiscal'] == 0){
					return redirect("/sempermissao");
				}
			}
			return $next($request);
		});
	}

	public function index(){
		$mdfes = Mdfe::
		where('estado', 'NOVO')
		->paginate(10);

		$menos30 = $this->menos30Dias();
		$date = date('d/m/Y');

		return view("mdfe/list")
		->with('mdfes', $mdfes)
		->with('mdfeEnvioJs', true)
		->with('links', true)
		->with('dataInicial', $menos30)
		->with('dataFinal', $date)
		->with('title', "Lista de MDF-e");

	}

	public function nova(){
		$lastMdfe = Mdfe::lastMdfe();
		$veiculos = Veiculo::all();
		$config = ConfigNota::first();
		$ufs = Mdfe::cUF();
		$tiposUnidadeTransporte = Mdfe::tiposUnidadeTransporte();


		if($config == null || count($veiculos) == 0){
			return view("cte/erro")
			->with('veiculos', $veiculos)
			->with('naturezas', true)
			->with('config', $config)
			->with('clienteCadastrado', true)
			->with('title', "Validação para Emitir");

		}else{
			return view("mdfe/register")

			->with('mdfeJs', true)
			->with('veiculos', $veiculos)
			->with('ufs', $ufs)
			->with('tiposUnidadeTransporte', $tiposUnidadeTransporte)
			->with('lastMdfe', $lastMdfe->mdfe_numero ?? 'Nulo')
			->with('title', "Nova MDF-e");
		}
	}

	private function menos30Dias(){
		return date('d/m/Y', strtotime("-30 days",strtotime(str_replace("/", "-", 
			date('Y-m-d')))));
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	public function salvar(Request $request){
		$data = $request->data;
		$infoDescarga = $data['infoDescarga'];
		$municipiosCarregamento = $data['municipios_carregamento'];
		$ciot = isset($data['ciot']) ? $data['ciot'] : null;
		$valePedagio = isset($data['vale_pedagio']) ? $data['vale_pedagio'] : null ;
		$percurso = $data['percurso'];
		$veiculoTracao = $data['veiculo_tracao'];
		$veiculoReboque = $data['veiculo_reboque'];
		$ufInicio = $data['uf_inicio'];
		$ufFim = $data['uf_fim'];
		$dataInicioViagem = $data['data_inicio_viagem'];
		$cargaPosterior = $data['carga_posteior'];
		$cnpjContratante = $data['cnpj_contratante'];
		$seguradoraNome = $data['seguradora_nome'] ?? '';
		$seguradraNumeroApolice = $data['seguradora_numero_apolice'] ?? '';
		$seguradoNumeroAverbacao = $data['seguradora_numero_averbacao'] ?? '';
		$seguradoraCnpj = $data['seguradora_cnpj'] ?? '';
		$valorCarga = str_replace(",", ".", $data['valor_carga']);
		$qtdCarga = str_replace(",", ".", $data['qtd_carga']);
		$infoComplementar = $data['info_complementar'] ?? '';
		$infoFisco = $data['info_fisco'] ?? '';

		$mdfe = Mdfe::create([
			'uf_inicio' => $ufInicio,
			'uf_fim' => $ufFim,
			'data_inicio_viagem' => $this->parseDate($dataInicioViagem),
			'carga_posterior' => $cargaPosterior,
			'veiculo_tracao_id' => $veiculoTracao, 
			'veiculo_reboque_id' => $veiculoReboque,
			'estado' => 'NOVO',
			'seguradora_nome' => $seguradoraNome,
			'seguradora_cnpj' => $seguradoraCnpj,
			'numero_apolice' => $seguradraNumeroApolice,
			'numero_averbacao' => $seguradoNumeroAverbacao,
			'valor_carga' => $valorCarga,
			'quantidade_carga' => $qtdCarga,
			'info_complementar' => $infoComplementar,
			'info_adicional_fisco' => $infoFisco,
			'cnpj_contratante' => $cnpjContratante,
			'mdfe_numero' => 0

		]);

		foreach($municipiosCarregamento as $m){
			MunicipioCarregamento::create([
				'cidade_id' => $m['id'],
				'mdfe_id' => $mdfe->id
			]);
		}

		foreach($percurso as $p){
			Percurso::create([
				'uf' => $p,
				'mdfe_id' => $mdfe->id
			]);
		}

		if($valePedagio != null){
			foreach($valePedagio as $v){
				ValePedagio::create([
					'mdfe_id' => $mdfe->id,
					'cnpj_fornecedor' => $v['cnpj_fornecedor'],
					'cnpj_fornecedor_pagador' => $v['doc_pagador'],
					'numero_compra' => $v['numero_compra'],
					'valor' => $v['valor']
				]);
			}
		}

		if($ciot != null){
			foreach($ciot as $c){
				Ciot::create([
					'mdfe_id' => $mdfe->id,
					'cpf_cnpj' => $c['documento'],
					'codigo' => $c['codigo']

				]);
			}
		}

		foreach($infoDescarga as $i){
			$info = InfoDescarga::create([
				'mdfe_id' => $mdfe->id,
				'tp_unid_transp' => $i['tpTransp'],
				'id_unid_transp' => $i['idUnidTransp'],
				'quantidade_rateio' => $i['qtdRateioTransp']
			]);

			NFeDescarga::Create([
				'info_id' => $info->id,
				'chave' => str_replace(" ", "", $i['chaveNFe']),
				'seg_cod_barras' => str_replace(" ", "", $i['segCodNFe'])
			]);

			CTeDescarga::Create([
				'info_id' => $info->id,
				'chave' => str_replace(" ", "", $i['chaveCTe']),
				'seg_cod_barras' => str_replace(" ", "", $i['segCodCTe'])
			]);

			foreach($i['lacresUnidCarga'] as $l){
				LacreUnidadeCarga::create([
					'info_id' => $info->id,
					'numero' => $l
				]);
			}

			foreach($i['lacresUnidTransp'] as $l){
				LacreTransporte::create([
					'info_id' => $info->id,
					'numero' => $l
				]);
			}

			UnidadeCarga::create([
				'info_id' => $info->id,
				'id_unidade_carga' => $i['idUnidCarga'],
				'quantidade_rateio' => $i['qtdRateioUnidCarga']
			]);
		}


		echo json_encode($mdfe);
	}
}
