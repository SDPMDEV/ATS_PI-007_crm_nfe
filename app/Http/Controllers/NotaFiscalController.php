<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ConfigNota;
use App\Services\NFService;
use App\Venda;
use App\ContaReceber;
use App\Certificado;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\DA\NFe\Daevento;
use Mail;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use NFePHP\POS\DanfcePos;

class NotaFiscalController extends Controller
{
	public function __construct(){
		$this->middleware(function ($request, $next) {
			$value = session('user_logged');
			if(!$value){
				return redirect("/login");
			}
			return $next($request);
		});
	}

	public function gerarNf(Request $request){

		$vendaId = $request->vendaId;
		$venda = Venda::
		where('id', $vendaId)
		->first();

		$config = ConfigNota::first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$nfe_service = new NFService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		]);

		if($venda->estado == 'REJEITADO' || $venda->estado == 'DISPONIVEL'){
			header('Content-type: text/html; charset=UTF-8');

			$nfe = $nfe_service->gerarNFe($vendaId);
			if(!isset($nfe['erros_xml'])){
			// file_put_contents('xml/teste2.xml', $nfe['xml']);
			// return response()->json($nfe, 200);
				$signed = $nfe_service->sign($nfe['xml']);
				$resultado = $nfe_service->transmitir($signed, $nfe['chave']);

				if(substr($resultado, 0, 4) != 'Erro'){
					$venda->chave = $nfe['chave'];
					$venda->path_xml = $nfe['chave'] . '.xml';
					$venda->estado = 'APROVADO';

					$venda->NfNumero = $nfe['nNf'];
					$venda->save();
				}else{
					$venda->estado = 'REJEITADO';
					$venda->save();
				}
				echo json_encode($resultado);
			}else{
				return response()->json($nfe['erros_xml'], 401);
			}

		}else{
			echo json_encode("Apro");
		}

	}

	public function inutilizar(Request $request){

		$config = ConfigNota::first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);


		$nfe_service = new NFService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		]);

		// echo json_encode($request->justificativa);
		$result = $nfe_service->inutilizar($request->nInicio, $request->nFinal, 
			$request->justificativa);

		echo json_encode($result);
	}


	public function consultaCadastro(Request $request){

		$config = ConfigNota::first();
		$certificado = Certificado::first();

		if($config == null || $certificado == null){
			return response()->json("Configure o emitente para buscar", 403);
		}

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);
		try{
			$nfe_service = new NFService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => (int)$config->ambiente,
				"razaosocial" => $config->razao_social,
				"siglaUF" => $config->UF,
				"cnpj" => $cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => $config->csc,
				"CSCid" => $config->csc_id
			]);
			$cnpj = $request->cnpj;
			$uf = $request->uf;
			$nfe_service->consultaCadastro($cnpj, $uf);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function imprimir($id){
		$venda = Venda::
		where('id', $id)
		->first();

		$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
		if(file_exists($public.'xml_nfe/'.$venda->chave.'.xml')){
			$xml = file_get_contents($public.'xml_nfe/'.$venda->chave.'.xml');
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'imgs/logo.jpg'));

			try {
				$danfe = new Danfe($xml);
				$id = $danfe->monta($logo);
				$pdf = $danfe->render();
			// header('Content-Type: application/pdf');
			// echo $pdf;
				return response($pdf)
				->header('Content-Type', 'application/pdf');
			} catch (InvalidArgumentException $e) {
				echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
			}  
		}else{
			echo "Arquivo XML não encontrado!!";
		}
	}

	public function escpos($id){
		$venda = Venda::
		where('id', $id)
		->first();

		$public = getenv('SERVIDOR_WEB') ? 'public/' : '';

		$xml = file_get_contents($public.'xml_nfe/'.$venda->chave.'.xml');
		$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'imgs/logo.jpg'));
		// $docxml = FilesFolders::readFile($xml);
		$connector = new NetworkPrintConnector('127.0.0.1', 9100);
		$danfcepos = new DanfcePos($connector);
		
	}

	public function imprimirCce($id){
		$venda = Venda::
		where('id', $id)
		->first();

		if($venda->sequencia_cce > 0){

			$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
			if(file_exists($public.'xml_nfe_correcao/'.$venda->chave.'.xml')){
				$xml = file_get_contents($public.'xml_nfe_correcao/'.$venda->chave.'.xml');
				$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'imgs/logo.jpg'));

				$dadosEmitente = $this->getEmitente();

				try {
					$daevento = new Daevento($xml, $dadosEmitente);
					$daevento->debugMode(true);
					$pdf = $daevento->render($logo);
				// header('Content-Type: application/pdf');
				// echo $pdf;
					return response($pdf)
					->header('Content-Type', 'application/pdf');
				} catch (InvalidArgumentException $e) {
					echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
				}  
			}else{
				echo "Arquivo XML não encontrado!!";
			}
		}else{
			echo "<center><h1>Este documento não possui evento de correção!<h1></center>";
		}
	}

	public function imprimirCancela($id){
		$venda = Venda::
		where('id', $id)
		->first();

		if($venda->estado == 'CANCELADO'){
			try {
				$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
				if(file_exists($public.'xml_nfe_cancelada/'.$venda->chave.'.xml')){
					$xml = file_get_contents($public.'xml_nfe_cancelada/'.$venda->chave.'.xml');

					$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'imgs/logo.jpg'));

					$dadosEmitente = $this->getEmitente();

					$daevento = new Daevento($xml, $dadosEmitente);
					$daevento->debugMode(true);
					$pdf = $daevento->render($logo);
				// header('Content-Type: application/pdf');
				// echo $pdf;
					return response($pdf)
					->header('Content-Type', 'application/pdf');
				}else{
					echo "Arquivo XML não encontrado!!";
				}
			} catch (InvalidArgumentException $e) {
				echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
			}  
		}else{
			echo "<center><h1>Este documento não possui evento de cancelamento!<h1></center>";
		}
	}

	private function getEmitente(){
		$config = ConfigNota::first();
		return [
			'razao' => $config->razao_social,
			'logradouro' => $config->logradouro,
			'numero' => $config->numero,
			'complemento' => '',
			'bairro' => $config->bairro,
			'CEP' => $config->cep,
			'municipio' => $config->municipio,
			'UF' => $config->UF,
			'telefone' => $config->telefone,
			'email' => ''
		];
	}

	public function cancelar(Request $request){

		$config = ConfigNota::first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$nfe_service = new NFService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		]);


		$nfe = $nfe_service->cancelar($request->id, $request->justificativa);

		if(!isset($nfe['erro'])){

			$venda = Venda::
			where('id', $request->id)
			->first();
			$venda->estado = 'CANCELADO';
			$venda->save();

			$this->removerDuplicadas($venda);
			return response()->json($nfe, 200);

		}else{
			return response()->json($nfe['data'], $nfe['status']);
		}
		
	}

	private function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	private function removerDuplicadas($venda){
		foreach($venda->duplicatas as $dp){
			$c = ContaReceber::
			where('id', $dp->id)
			->delete();
		}
	}

	public function cartaCorrecao(Request $request){

		$config = ConfigNota::first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$nfe_service = new NFService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		]);


		$nfe = $nfe_service->cartaCorrecao($request->id, $request->correcao);
		echo json_encode($nfe);
	}


	public function consultar(Request $request){
		$config = ConfigNota::first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);
		$nfe_service = new NFService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		]);
		$c = $nfe_service->consultar($request->id);
		echo json_encode($c);
	}

	public function consultar_cliente($id){
		$venda = Venda::
		where('id', $id)
		->first();
		echo json_encode($venda->cliente);
	}
	
	public function enviarXml(Request $request){
		$email = $request->email;
		$id = $request->id;
		$venda = Venda::
		where('id', $id)
		->first();
		$this->criarPdfParaEnvio($venda);
		$value = session('user_logged');
		Mail::send('mail.xml_send', ['emissao' => $venda->data_registro, 'nf' => $venda->NfNumero,
			'valor' => $venda->valor_total, 'usuario' => $value['nome']], function($m) use ($venda, $email){

				$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
				$nomeEmpresa = getenv('MAIL_NAME');
				$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
				$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
				$emailEnvio = getenv('MAIL_USERNAME');

				$m->from($emailEnvio, $nomeEmpresa);
				$m->subject('Envio de XML NF ' . $venda->NfNumero);
				
				$m->attach($public.'xml_nfe/'.$venda->chave.'.xml');
				$m->attach($public.'pdf/DANFE.pdf');
				$m->to($email);
			});
		return "ok";
	}

	private function criarPdfParaEnvio($venda){
		$public = getenv('SERVIDOR_WEB') ? 'public/' : '';
		$xml = file_get_contents($public.'xml_nfe/'.$venda->chave.'.xml');
		$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'imgs/logo.jpg'));
		// $docxml = FilesFolders::readFile($xml);

		try {
			$danfe = new Danfe($xml);
			$id = $danfe->monta($logo);
			$pdf = $danfe->render();
			header('Content-Type: application/pdf');
			file_put_contents($public.'pdf/DANFE.pdf',$pdf);
		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}
}
