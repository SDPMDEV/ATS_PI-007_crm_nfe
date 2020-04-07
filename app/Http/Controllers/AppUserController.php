<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ClienteDelivery;
use \Illuminate\Support\Facades\Validator;
use App\ProdutoDelivery;
use App\PedidoDelivery;
use App\EnderecoDelivery;
use App\TokenClienteDelivery;
use App\Rules\CelularDup;
use App\Rules\EmailDup;
use Comtele\Services\TextMessageService;
use App\CodigoDesconto;

class AppUserController extends Controller
{
	public function testeConn(){
		return response()->json("ok", 200);
	}

	public function all(){
		$clientes = ClienteDelivery::all();
		$arr = array();
		foreach($clientes as $c){
			$arr[$c->id. ' - ' .$c->nome] = null;
                //array_push($arr, $temp);
		}
		echo json_encode($arr);
	}

	public function signup(Request $request){
		
		$validator = Validator::make($request->all(), [
			'nome' => 'required|max:30',
			'sobre_nome' => 'required|max:30',
			'email' => ['required','max:50', 'email',new EmailDup],
			'senha' => 'required|max:80',
			'celular' => ['required','min:13', 'max:15',new CelularDup]
		],[
			'nome.required' => 'Nome é obrigatório.',
			'nome.max' => '30 caracteres maximos permitidos.',
			'sobre_nome.required' => 'Sobre nome é obrigatório.',
			'sobre_nome.max' => '30 caracteres maximos permitidos.',

			'email.required' => 'Email é obrigatório.',
			'email.max' => '40 caracteres maximos permitidos.',
			'email.email' => 'Email inválido.',

			'celular.required' => 'O campo celular é obrigatório.',
			'celular.min' => 'Minimo de 15 caracteres',
			'celular.max' => 'Maximo de 15 caracteres',

		]);

		if($validator->fails()){
			
			// echo json_encode($validator->errors());
			return response()->json($validator->errors(), 400);
		}else{
			$cod = rand(100000, 888888);
			// $celular = $request->celular;
			// $celular = str_replace(" ", "", $celular);
			// $celular = str_replace("-", "", $celular);
			// $this->sendSms($celular, $cod);
			$request->merge([ 'senha' => md5($request->senha)]);
			$request->merge([ 'ativo' => false]);
			$request->merge([ 'token' => $cod]);
			$result = ClienteDelivery::create($request->all());

			return response()->json($result, 200);
		}
	}

	private function sendSms($phone, $cod){
		$nomeEmpresa = getenv('SMS_NOME_EMPRESA');
		$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
		$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
		$content = $nomeEmpresa. " codigo de Autorizacao ". $cod;
		$textMessageService = new TextMessageService(getenv('SMS_KEY'));
		$res = $textMessageService->send("Sender", $content, [$phone]);
		return $res;
	}

	public function login(Request $request){
		$validator = Validator::make($request->all(), [
			'mail_phone' => 'required',
			'senha' => 'required'
		],[
			'mail_phone.required' => 'Obrigatório.',
			'senha.required' => 'Obrigatório.',

		]);

		if($validator->fails()){
			return response()->json($validator->errors(), 400);
		}else{

			$mailPhone = $request->mail_phone;
			$senha = md5($request->senha);
			$cliente = null;
			if(is_numeric($mailPhone)){

				$cliente = ClienteDelivery::where('celular', $this->setaMascaraPhone($mailPhone))
				->where('senha', $senha)
				->first();

			}else{
				$cliente = ClienteDelivery::where('email', $mailPhone)
				->where('senha', $senha)
				->first();
			}

			if($cliente == null){
				return response()->json(['erro' => 'Credenciais inválidas'], 400);
			}else{
				if(!$cliente->ativo){
					return response()->json($cliente, 403);
				}
				$b64 = base64_encode("$cliente->nome;$cliente->id;$cliente->email");
				return response()->json($b64, 200);
			}
		}
	}

	private function setaMascaraPhone($phone){
		$n = substr($phone, 0, 2) . " ";
		$n .= substr($phone, 2, 5)."-";
		$n .= substr($phone, 7, 4);
		return $n;
	}

	public function enderecos($usuarioId){
		$cliente = ClienteDelivery::
		where('id', $usuarioId)
		->first();
		if(count($cliente->enderecos) > 0) return response()->json($cliente->enderecos, 200);
		else return response()->json(null, 204);
	}

	public function novoEndereco(Request $request){
		$result = EnderecoDelivery::create([
			'cliente_id' => $request->cliente, 
			'rua' => $request->rua,
			'numero' => $request->numero,
			'bairro' => $request->bairro, 
			'referencia' => $request->referencia,
			'latitude' => $request->latitude ?? '',
			'longitude' => $request->longitude ?? ''
		]);
		if($result) return response()->json($result, 200);
		else return response()->json(null, 204);
	}

	public function saveToken(Request $request){
		$result = TokenClienteDelivery::create([
			'cliente_id' => $request->cliente > 0 ? $request->cliente : null, 
			'token' => $request->token
		]);

		if($result) return response()->json($result, 200);
		else return response()->json(null, 204);
	}

	public function atualizaToken(Request $request){
		$res = TokenClienteDelivery::
		where('cliente_id', $request->cliente)
		->first();

		if($res == null){
			$res = TokenClienteDelivery::
			where('token', $request->token)
			->first();
		}

		if($res){
			$res->token = $request->token;
			$res->cliente_id = $request->cliente;
			$result = $res->save();
			return response()->json($res, 200);
		}else{
			return response()->json(null, 204);
		}
	}

	public function appComToken(Request $request){
		$res = TokenClienteDelivery::
		where('token', $request->token)
		->first();
		if($res) return response()->json($res, 200);
		else return response()->json(false, 204);
	}

	public function validaToken(Request $request){
		$token = $request->token;
		$id = $request->id;

		$cliente = ClienteDelivery::where('id', $id)
		->first();

		if($cliente->token == $token){
			$cliente->ativo = true;
			$cliente->save();

			$b64 = base64_encode("$cliente->nome;$cliente->id;$cliente->email");
			return response()->json($b64, 200);
		}else{
			return response()->json(false, 204);
		}
	}

	public function refreshToken(Request $request){
		$cliente = ClienteDelivery::where('id', $request->id)
		->first();
		$cod = rand(100000, 888888);

		$celular = $cliente->celular;
		$celular = str_replace(" ", "", $celular);
		$celular = str_replace("-", "", $celular);
		$cliente->token = $cod;
		$this->sendSms($celular, $cod);
		if($cliente->save())
			return response()->json($cliente, 200);
		else 
			return response()->json(false, 204);
	}

	public function validaCupom(Request $request){
		$cupom = CodigoDesconto::
		where('codigo', $request->cupom)
		->where('ativo', true)
		->first();

		if($cupom != null){
			if($cupom->cliente_id != $request->cliente){
				if($this->validaClienteUsouCupom($request->cliente, $cupom))
					$cupom = null;

			}else{
				$cupom = null;
			}
		}


		if($cupom != null)
			return response()->json($cupom, 200);
		else
			return response()->json(false, 204);

	}

	private function validaClienteUsouCupom($cliente, $cupom){

		$pedido = PedidoDelivery::
		where('cliente_id', $cliente)
		->where('cupom_id', $cupom->id)
		->get();

		return (count($pedido) > 0) ? true : false;
	}

}
