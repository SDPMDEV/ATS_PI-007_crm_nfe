<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ConfigNota;
use App\Certificado;
use App\NaturezaOperacao;

class ConfigNotaController extends Controller
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

	function sanitizeString($str){
		return preg_replace('{\W}', ' ', preg_replace('{ +}', ' ', strtr(
			utf8_decode(html_entity_decode($str)),
			utf8_decode('ÀÁÃÂÉÊÍÓÕÔÚÜÇÑàáãâéêíóõôúüçñ'),
			'AAAAEEIOOOUUCNaaaaeeiooouucn')));
	}

	public function index(){
		$naturezas = NaturezaOperacao::all();
		$tiposPagamento = ConfigNota::tiposPagamento();
		$tiposFrete = ConfigNota::tiposFrete();
		$listaCSTCSOSN = ConfigNota::listaCST();
		$listaCSTPISCOFINS = ConfigNota::listaCST_PIS_COFINS();
		$listaCSTIPI = ConfigNota::listaCST_IPI();
		$config = ConfigNota::first();
		$certificado = Certificado::first();
		$cUF = ConfigNota::cUF();
		return view('configNota/index')
		->with('config', $config)
		->with('naturezas', $naturezas)
		->with('tiposPagamento', $tiposPagamento)
		->with('tiposFrete', $tiposFrete)

		->with('listaCSTCSOSN', $listaCSTCSOSN)
		->with('listaCSTPISCOFINS', $listaCSTPISCOFINS)
		->with('listaCSTIPI', $listaCSTIPI)
		->with('cUF', $cUF)

		->with('certificado', $certificado)
		->with('title', 'Configurar Emitente');
	}


	public function save(Request $request){
		$this->_validate($request);
		$uf = $request->uf;
		if($request->id == 0){
			$result = ConfigNota::create([
				'razao_social' => strtoupper($this->sanitizeString($request->razao_social)),
				'nome_fantasia' => strtoupper($this->sanitizeString($request->nome_fantasia)),
				'cnpj' => $request->cnpj,
				'ie' => $request->ie,
				'logradouro' => strtoupper($this->sanitizeString($request->logradouro)),
				'numero' => strtoupper($this->sanitizeString($request->numero)),
				'bairro' => strtoupper($this->sanitizeString($request->bairro)),
				'cep' => $request->cep,
				'municipio' => strtoupper($this->sanitizeString($request->municipio)),
				'codMun' => $request->codMun,
				'codPais' => $request->codPais,
				'UF' => ConfigNota::getUF($uf),
				'pais' => strtoupper($request->pais),
				'fone' => $this->sanitizeString($request->fone),
				'CST_CSOSN_padrao' => $request->CST_CSOSN_padrao, 
				'CST_COFINS_padrao' => $request->CST_COFINS_padrao, 
				'CST_PIS_padrao' => $request->CST_PIS_padrao, 
				'CST_IPI_padrao' => $request->CST_IPI_padrao, 
				'frete_padrao' => $request->frete_padrao, 
				'tipo_pagamento_padrao' => $request->tipo_pagamento_padrao, 
				'nat_op_padrao' => $request->nat_op_padrao ?? 0, 
				'ambiente' => $request->ambiente, 
				'ultimo_numero_nfe' => $request->ultimo_numero_nfe, 
				'ultimo_numero_nfce' => $request->ultimo_numero_nfce, 
				'ultimo_numero_cte' => $request->ultimo_numero_cte, 
				'ultimo_numero_mdfe' => $request->ultimo_numero_mdfe
			]);
		}else{
			$config = ConfigNota::
			first();

			$config->razao_social = strtoupper($this->sanitizeString($request->razao_social));
			$config->nome_fantasia = strtoupper($this->sanitizeString($request->nome_fantasia));
			$config->cnpj = $this->sanitizeString($request->cnpj);
			$config->ie = $this->sanitizeString($request->ie);
			$config->logradouro = strtoupper($this->sanitizeString($request->logradouro));
			$config->numero = strtoupper($this->sanitizeString($request->numero));
			$config->bairro = strtoupper($this->sanitizeString($request->bairro));
			$config->cep = $request->cep;
			$config->municipio = strtoupper($this->sanitizeString($request->municipio));
			$config->codMun = $request->codMun;
			$config->codPais = $request->codPais;
			$config->UF = ConfigNota::getUF($uf);
			$config->pais = strtoupper($request->pais);
			$config->fone = $request->fone;

			$config->CST_CSOSN_padrao = $request->CST_CSOSN_padrao;
			$config->CST_COFINS_padrao = $request->CST_COFINS_padrao;
			$config->CST_PIS_padrao = $request->CST_PIS_padrao;
			$config->CST_IPI_padrao = $request->CST_IPI_padrao;
			
			$config->frete_padrao = $request->frete_padrao;
			$config->tipo_pagamento_padrao = $request->tipo_pagamento_padrao;
			$config->nat_op_padrao = $request->nat_op_padrao ?? 0;
			$config->ambiente = $request->ambiente;
			$config->cUF = $uf;
			$config->ultimo_numero_nfe = $request->ultimo_numero_nfe;
			$config->ultimo_numero_nfce = $request->ultimo_numero_nfce; 
			$config->ultimo_numero_cte = $request->ultimo_numero_cte;
			$config->ultimo_numero_mdfe = $request->ultimo_numero_mdfe;

			$result = $config->save();
		}

		if($result){
			session()->flash('color', 'blue');
			session()->flash("message", "Configurado com sucesso!");
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro ao configurar!');
		}

		return redirect('/configNF');
	}


	private function _validate(Request $request){
		$rules = [
			'razao_social' => 'required|max:100',
			'nome_fantasia' => 'required|max:80',
			'cnpj' => 'required',
			'ie' => 'required',
			'logradouro' => 'required|max:80',
			'numero' => 'required|max:10',
			'bairro' => 'required|max:50',
			'fone' => 'required|max:20',
			'cep' => 'required',
			'municipio' => 'required',
			'pais' => 'required',
			'pais' => 'required',
			'codPais' => 'required',
			'codMun' => 'required',
			'uf' => 'required|max:2|min:2',
			'rntrc' => 'max:12',
			'ultimo_numero_nfe' => 'required',
			'ultimo_numero_nfce' => 'required',
			'ultimo_numero_cte' => 'required',
			'ultimo_numero_mdfe' => 'required'
		];

		$messages = [
			'razao_social.required' => 'O Razão social nome é obrigatório.',
			'razao_social.max' => '100 caracteres maximos permitidos.',
			'nome_fantasia.required' => 'O campo Nome Fantasia é obrigatório.',
			'nome_fantasia.max' => '80 caracteres maximos permitidos.',
			'cnpj.required' => 'O campo CNPJ é obrigatório.',
			'logradouro.required' => 'O campo Logradouro é obrigatório.',
			'ie.required' => 'O campo Inscrição Estadual é obrigatório.',
			'logradouro.max' => '80 caracteres maximos permitidos.',
			'numero.required' => 'O campo Numero é obrigatório.',
			'cep.required' => 'O campo CEP é obrigatório.',
			'municipio.required' => 'O campo Municipio é obrigatório.',
			'numero.max' => '10 caracteres maximos permitidos.',
			'bairro.required' => 'O campo Bairro é obrigatório.',
			'bairro.max' => '50 caracteres maximos permitidos.',
			'fone.required' => 'O campo Telefone é obrigatório.',
			'fone.max' => '20 caracteres maximos permitidos.',

			'uf.required' => 'O campo UF é obrigatório.',

			'pais.required' => 'O campo Pais é obrigatório.',
			'codPais.required' => 'O campo Código do Pais é obrigatório.',
			'codMun.required' => 'O campo Código do Municipio é obrigatório.',
			'rntrc.max' => '12 caracteres maximos permitidos.',
			'ultimo_numero_nfe.required' => 'Campo obrigatório.',
			'ultimo_numero_nfe.required' => 'Campo obrigatório.',
			'ultimo_numero_nfce.required' => 'Campo obrigatório.',
			'ultimo_numero_cte.required' => 'Campo obrigatório.',
			'ultimo_numero_mdfe.required' => 'Campo obrigatório.'



		];
		$this->validate($request, $rules, $messages);
	}

	public function certificado(){
		return view('configNota/upload')
		->with('title', 'Upload de Certificado');
	}

	public function saveCertificado(Request $request){

		if($request->hasFile('file') && strlen($request->senha) > 0){
			$file = $request->file('file');
			$temp = file_get_contents($file);


			$res = Certificado::create([
				'senha' => $request->senha,
				'arquivo' => $temp
			]);
			if($res){
				session()->flash('color', 'green');
				session()->flash("message", "Upload de certificado realizado!");
				return redirect('/configNF');
				
			}
		}else{
			session()->flash('color', 'red');
			session()->flash("message", "Envie o arquivo e senha por favor!");
			return redirect('/configNF/certificado');
		}
	}

}
