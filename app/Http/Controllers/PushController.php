<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Push;
use App\TokenClienteDelivery;
use App\TokenWeb;

class PushController extends Controller
{	
	public function __construct(){
		$this->middleware(function ($request, $next) {
			$value = session('user_logged');
			if(!$value){
				return redirect("/login");
			}else{
				if($value['acesso_produto'] == 0){
					return redirect("/sempermissao");
				}
			}
			return $next($request);
		});
	}

	public function index(){

    	// $to = 'dNO6h_mjTck:APA91bFaD2869FjFV6ta02JClhnO-nvhDTL-cpFZRCi7b9BvAmMPTu70OtiZRqCSKlprolzRnM6_V_v1TweU_aQPmtbVJtg8UwwaVBEuWaWbIcUaEn_vFAvrLv5q-6WJv6EB3zl357Kc';
    	// $data = array(
    	// 	'message' => 'FCM SENDER'
    	// );
    	// $t = $this->send($to, $data);
    	// echo $t;

		$pushes = Push::
		orderBy('id', 'desc')
		->get();
		return view('push/index')
		->with('pushes', $pushes)
		->with('title', 'Push');
	}

	public function new(){
		return view('push/new')
		->with('pushJs', true)
		->with('title', 'Nova Push');
	}

	public function save(Request $request){
		$this->_validate($request);
		$cli = explode("-", $request->cli);
		$cli = $cli[0];

		$res = Push::create([
			'titulo' => $request->titulo,
			'texto' => $request->texto,
			'cliente_id' => $request->todos ? NULL : $cli,
			'status' => false
		]);

		if($res){
			session()->flash('color', 'blue');
			session()->flash('message', 'Push adicionado!');
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro!');
		}
		return redirect('/push');
	}

	private function _validate(Request $request){

		$rules = [
			'cli' => ($request->id > 0) ? '' : ($request->todos ? '' : 'required'),
			'titulo' => 'required|max:50',
			'texto' => 'required|max:100'
		];

		$messages = [
			'titulo.required' => 'O campo titulo é obrigatório.',
			'titulo.max' => '50 caracteres maximos permitidos.',
			'texto.required' => 'O campo texto é obrigatório.',
			'texto.max' => '100 caracteres maximos permitidos.',
			'cli.required' => 'O campo cliente é obrigatório.'
		];
		$this->validate($request, $rules, $messages);
	}

	public function edit($id){
		$push = Push::
		where('id', $id)
		->first();

		return view('push/new')
		->with('pushJs', true)
		->with('push', $push)
		->with('title', 'Nova Push');
	}

	public function update(Request $request){
		$this->_validate($request);
		$push = Push::
		where('id', $request->id)
		->first();

		$push->titulo = $request->titulo;
		$push->texto = $request->texto;
		if($push->save()){
			session()->flash('color', 'green');
			session()->flash('message', 'Push editado!');
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro!');
		}

		return redirect('/push');

	}

	public function send($id){
		$push = Push::
		where('id', $id)
		->first();

		$push->status = true;
		// $push->save();

		$tkTemp = [];
		if($push->cliente){ 

			foreach($push->cliente->tokens as $t){

				if(!in_array($t->token, $tkTemp)){
    			// send
					$data = array(
						'message' => $push->texto,
						'title' => $push->titulo
					);

					$this->sendGo($t->token, $data);

					array_push($tkTemp, $t->token);
				}
			}
			session()->flash('color', 'blue');
			session()->flash('message', 'Push enviado!');


			return redirect('/push');

		}else{
			$todos = TokenClienteDelivery::all();
			$tkTemp = [];
			foreach($todos as $td){

				if(!in_array($td->token, $tkTemp)){
					
					array_push($tkTemp, $td->token);

				}
			}
			$data = array(
				'message' => $push->texto,
				'title' => $push->titulo
			);

			$this->sendGo($tkTemp, $data);

			$todos = TokenWeb::all();

			foreach($todos as $td){
				$data = array(
					'body' => $push->texto,
					'title' => $push->titulo,
					'click_action' => getenv('PATH_URL'),
					'icon' => 'imgs/logo.png'
				);
				$this->sendWeb($td->token, $data);

			}
			
			$push->status = true;
			$push->save();
			session()->flash('color', 'blue');
			session()->flash('message', 'Pushs enviados!');
			return redirect('/push');
		}
	}

	private function sendGo($to = '', $data = array()){
		$apiKey = getenv('PUSH_KEY');
		$fields = array('registration_ids' => [$to], 'data' => $data);

		$headers = array('Authorization:key = '.$apiKey, 'Content-Type: application/json');

		$url = 'https://fcm.googleapis.com/fcm/send';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		$result = curl_exec($ch);
		if($result === FALSE){
			die('Curl failed: ' . curl_error($ch));
		}
		curl_close($ch);
		return $result;
	}

	private function sendWeb($to = '', $data = array()){
		$apiKey = getenv('PUSH_KEY');
		$fields = array('to' => $to, 'notification' => $data);

		$headers = array('Authorization:key = '.$apiKey, 'Content-Type: application/json');

		$url = 'https://fcm.googleapis.com/fcm/send';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		$result = curl_exec($ch);
		if($result === FALSE){
			die('Curl failed: ' . curl_error($ch));
		}
		curl_close($ch);
		return $result;
	}

	public function delete($id){
		$res = Push::
		where('id', $id)
		->delete();

		if($res){
			session()->flash('color', 'blue');
			session()->flash('message', 'Push removido!');
		}else{

			session()->flash('color', 'red');
			session()->flash('message', 'Erro!');
		}
		return redirect('/push');
	}

}
