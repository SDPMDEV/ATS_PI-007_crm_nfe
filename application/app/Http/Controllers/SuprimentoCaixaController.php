<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SuprimentoCaixa;
use App\AberturaCaixa;
class SuprimentoCaixaController extends Controller
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

	public function save(Request $request){
		$result = SuprimentoCaixa::create([
			'usuario_id' => get_id_user(),
			'valor' => str_replace(",", ".", $request->valor),
			'observacao' => $request->obs
		]);
		echo json_encode($result);

	}

	public function diaria(){
		$ab = AberturaCaixa::where('ultima_venda', 0)->orderBy('id', 'desc')->first();

		date_default_timezone_set('America/Sao_Paulo');
		$hoje = date("Y-m-d") . " 00:00:00";
		$amanha = date('Y-m-d', strtotime('+1 days')). " 00:00:00";
		$sangrias = SuprimentoCaixa::
		whereBetween('created_at', [$ab->created_at, 
			$amanha])
		->get();
		echo json_encode($this->setUsuario($sangrias));
	}

	private function setUsuario($sangrias){
		for($aux = 0; $aux < count($sangrias); $aux++){
			$sangrias[$aux]['nome_usuario'] = $sangrias[$aux]->usuario->nome;
		}
		return $sangrias;
	}

}
