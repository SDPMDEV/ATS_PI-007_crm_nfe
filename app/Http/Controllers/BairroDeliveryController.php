<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BairroDelivery;
class BairroDeliveryController extends Controller
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
		$bairros = BairroDelivery::all();
		return view('bairros/list')
		->with('bairros', $bairros)
		->with('title', 'Bairros');
	}

	public function new(){
		return view('bairros/register')
		->with('title', 'Cadastrar Bairro');
	}

	public function save(Request $request){
		$bairro = new BairroDelivery();
		$this->_validate($request);

		$request->merge(['valor_entrega' => str_replace(",", ".", $request->valor_entrega)]);
		$request->merge(['valor_repasse' => $request->valor_repasse ? str_replace(",", ".", $request->valor_repasse) : 0]);

		$result = $bairro->create($request->all());

		if($result){

			session()->flash("mensagem_sucesso", "Bairro cadastrado com sucesso.");
		}else{

			session()->flash('mensagem_erro', 'Erro ao cadastrar bairro.');
		}

		return redirect('/bairrosDelivery');
	}

	public function edit($id){
		$bairro = new BairroDelivery(); 

		$resp = $bairro
		->where('id', $id)->first();  

		return view('bairros/register')
		->with('bairro', $resp)
		->with('title', 'Editar Bairro');

	}

	public function update(Request $request){
		$bairro = new BairroDelivery();
		$request->merge(['valor_entrega' => str_replace(",", ".", $request->valor_entrega)]);
		
		$id = $request->input('id');
		$resp = $bairro
		->where('id', $id)->first(); 

		$this->_validate($request);


		$resp->nome = $request->input('nome');
		$resp->valor_entrega = $request->input('valor_entrega');
		$resp->valor_repasse = $request->input('valor_repasse');

		$result = $resp->save();
		if($result){

			session()->flash('mensagem_sucesso', 'Bairro editado com sucesso!');
		}else{

			session()->flash('mensagem_erro', 'Erro ao editar bairro!');
		}

		return redirect('/bairrosDelivery'); 
	}

	public function delete($id){

		$delete = BairroDelivery
		::where('id', $id)
		->delete();
		if($delete){

			session()->flash('mensagem_sucesso', 'Registro removido!');
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/bairrosDelivery');

	}


	private function _validate(Request $request){
		$rules = [
			'nome' => 'required|max:50',
			'valor_entrega' => 'required'
		];

		$messages = [
			'nome.required' => 'O campo nome é obrigatório.',
			'nome.max' => '50 caracteres maximos permitidos.',
			'valor_entrega.required' => 'O campo valor de entrega é obrigatório.',

		];
		$this->validate($request, $rules, $messages);
	}
}
