<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\MDFeService;
use App\ConfigNota;
use App\Mdfe;
use Mail;

class EmiteMdfeController extends Controller
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

	public function enviar(Request $request){

		$mdfe = Mdfe::where('id', $request->id)
		->first();

		$config = ConfigNota::first();
		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$mdfe_service = new MDFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 2,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"inscricaomunicipal" => "11223",
			"codigomunicipio" => "3518800",
			"schemes" => "PL_MDFe_300a",
			"versao" => '3.00'
		]);

		$xml = $mdfe_service->gerar($mdfe);
		$resultado = false;

		if($mdfe->estado == 'NOVO' || $mdfe->estado == 'DISPONIVEL'){
			header('Content-type: text/html; charset=UTF-8');
			$xml = $mdfe_service->gerar($mdfe);

			$signed = $mdfe_service->sign($xml);


			$resultado = $mdfe_service->transmitir($signed);

		// 	if(substr($resultado, 0, 4) != 'Erro'){
		// 		$cteEmit->chave = $cte['chave'];
		// 		$cteEmit->path_xml = $cte['chave'] . '.xml';
		// 		$cteEmit->estado = 'APROVADO';

		// 		$cteEmit->cte_numero = $cte['nCte'];
		// 		$cteEmit->save();
		// 	}else{
		// 		$cteEmit->estado = 'REJEITADO';
		// 		$cteEmit->save();
		// 	}
		// 	echo json_encode($resultado);
		// }else{
		// 	echo json_encode("Apro");
		}
		echo json_encode($resultado);
	}

}
