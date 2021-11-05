<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Motoboy;
use App\PedidoMotoboy;

class MotoboyController extends Controller
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

	public function index(){
		$motoboys = Motoboy::all();
		return view('motoboys/list')
		->with('motoboys', $motoboys)
		->with('title', 'Motoboys');
	}

	public function new(){
		return view('motoboys/register')
		->with('title', 'Cadastrar Motoboy');
	}

	public function save(Request $request){

		$this->_validate($request);

		$request->merge(['telefone2' => $request->telefone2 ?? '']);
		$request->merge(['telefone3' => $request->telefone3 ?? '']);


		$result = Motoboy::create($request->all());


		if($result){

			session()->flash("mensagem_sucesso", "Bairro cadastrado com sucesso.");
		}else{

			session()->flash('mensagem_erro', 'Erro ao cadastrar bairro.');
		}

		return redirect('/motoboys');
	}

	public function edit($id){
		$resp = Motoboy::find($id);

		return view('motoboys/register')
		->with('motoboy', $resp)
		->with('title', 'Editar Motoboy');

	}

	public function update(Request $request){
		
		$id = $request->input('id');
		$resp = Motoboy::find($id);

		$this->_validate($request);


		$resp->nome = $request->nome;
		$resp->telefone1 = $request->telefone1;
		$resp->telefone2 = $request->telefone2;
		$resp->telefone3 = $request->telefone3;
		$resp->cpf = $request->cpf;
		$resp->rg = $request->rg;
		$resp->endereco = $request->endereco;
		$resp->tipo_transporte = $request->tipo_transporte;


		$result = $resp->save();
		if($result){

			session()->flash('mensagem_sucesso', 'Motoboy editado com sucesso!');
		}else{

			session()->flash('mensagem_erro', 'Erro ao editar Motoboy!');
		}

		return redirect('/motoboys'); 
	}

	private function _validate(Request $request){
		$rules = [
			'nome' => 'required|max:60',
			'telefone1' => 'required',
			'cpf' => 'required',
			'rg' => 'required',
			'endereco' => 'required'
		];

		$messages = [
			'nome.required' => 'O campo nome é obrigatório.',
			'nome.max' => '60 caracteres maximos permitidos.',
			'telefone1.required' => 'Campo obrigatório',
			'cpf.required' => 'Campo obrigatório',
			'rg.required' => 'Campo obrigatório',
			'endereco.required' => 'Campo obrigatório',

		];
		$this->validate($request, $rules, $messages);
	}

	public function entregas(){
		$entregas = PedidoMotoboy::
		orderBy('id', 'desc')
		->limit(50)
		->get();

		$motoboys = Motoboy::all();

		return view('motoboys/entregas')
		->with('entregas', $entregas)
		->with('motoboys', $motoboys)
		->with('motoboyEntrega', true)
		->with('title', 'Entregas');
	}

	public function deleteEntrega($id){
		$entrega = PedidoMotoboy::find($id);
		if($entrega->delete()){

			session()->flash('mensagem_sucesso', 'Entrega removida com sucesso!');
		}else{

			session()->flash('mensagem_erro', 'Erro ao remover entrega!');
		}
		return redirect('/entregas');
	}

	public function filtro(Request $request){
		$motoboyId = $request->motoboy_id;
		$status = $request->status;
		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;


		$entregas = PedidoMotoboy::
		orderBy('id', 'desc');

		if($status != '--'){
			$entregas->where('status', $status);
		}

		if($dataFinal && $dataInicial){
			$data_inicial = $this->parseDate($request->data_inicial);
			$data_final = $this->parseDate($request->data_final, true);
			$entregas->whereBetween('created_at', [$data_inicial, 
				$data_final]);
		}

		if($motoboyId != '--'){
			$entregas->where('motoboy_id', $motoboyId);
		}
		$entregas = $entregas->get();



		$motoboys = Motoboy::all();

		return view('motoboys/entregas')
		->with('entregas', $entregas)
		->with('motoboys', $motoboys)
		->with('motoboyEntrega', true)
		->with('title', 'Entregas');
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	public function pagar(Request $request){
		try{
			$vArr = $arr = $request->arr;
			$arr = explode(",", $arr);

			foreach($arr as $a){

				$pedido = PedidoMotoboy::find($a);
				$pedido->status = 1;
				$pedido->save();
			}
			session()->flash('mensagem_sucesso', 'Entrega(s) paga(s) com sucesso!');
		}catch(\Exception $e){
			session()->flash('mensagem_erro', 'Erro ao pagar entrega(s)!');

		}
		return redirect()->back();

	}
}
