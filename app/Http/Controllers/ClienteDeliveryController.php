<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ClienteDelivery;
use App\EnderecoDelivery;
use App\Rules\CelularDup;
use App\DeliveryConfig;

class ClienteDeliveryController extends Controller
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

		$clientes = ClienteDelivery::
		orderBy('id', 'desc')
		->paginate(20);

		return view('clienteDelivery/list')
		->with('title', 'Clientes')
		->with('clientes', $clientes)
		->with('links', true);
	}

	public function edit($id){
		$cliente = ClienteDelivery::
		where('id', $id)
		->first();

		return view('clienteDelivery/register')
		->with('title', 'Editar Cliente')
		->with('cliente', $cliente);
	}

	public function update(Request $request){
		
		$cliente = ClienteDelivery::
		where('id', $request->id)
		->first();
		$this->_validate($request, $cliente->celular);
		$cliente->celular = $request->celular;
		$cliente->email = $request->email;
		$cliente->nome = $request->nome;
		$cliente->sobre_nome = $request->sobre_nome;

		if($cliente->save()){
			session()->flash('color', 'green');
            session()->flash("message", "Cliente editado com sucesso!");
        }else{
            session()->flash('color', 'red');
            session()->flash('message', 'Erro ao editar cliente!');
        }
		return redirect('/clientesDelivery');
	}

	public function pedidos($id){
		$cliente = ClienteDelivery::
		where('id', $id)
		->first();

		return view('clienteDelivery/vendas')
		->with('title', 'Pedidos de Cliente')
		->with('cliente', $cliente);
	}

	public function enderecos($id){
		$cliente = ClienteDelivery::
		where('id', $id)
		->first();

		return view('clienteDelivery/enderecos')
		->with('title', 'Endereços do Cliente')
		->with('cliente', $cliente);
	}

	public function enderecoEdit($id){
		$endereco = EnderecoDelivery::
		where('id', $id)
		->first();

		return view('clienteDelivery/enderecoEdit')
		->with('title', 'Editar endereço')
		->with('endereco', $endereco);
	}

	public function enderecosMap($id){
		$endereco = EnderecoDelivery::
		where('id', $id)
		->first();

		$config = DeliveryConfig::first();

		return view('clienteDelivery/enderecoMap')
		->with('title', 'ver Mapa')
		->with('mapJs', true)
		->with('config', $config)
		->with('endereco', $endereco);
	}

	public function updateEndereco(Request $request){
		$end = EnderecoDelivery::
		where('id', $request->id)
		->first();
		$this->_validateEnd($request);
		$end->rua = $request->rua;
		$end->numero = $request->numero;
		$end->bairro = $request->bairro;
		$end->referencia = $request->referencia;
		$end->latitude = $request->latitude ?? '';
		$end->latitude = $request->latitude ?? '';

		if($end->save()){
			session()->flash('color', 'green');
            session()->flash("message", "Endereço editado com sucesso!");
        }else{
            session()->flash('color', 'red');
            session()->flash('message', 'Erro ao editar endereço!');
        }
		return redirect('clientesDelivery/enderecos/'.$end->id);
	}

	private function _validate(Request $request, $celularAnterior){
		$rules = [
			'nome' => 'required|max:30',
			'sobre_nome' => 'required|max:30',
			'celular' => ['required','min:13', 'max:15', 


			$request->celular == $celularAnterior ? null :
			new CelularDup ],
			'email' => 'required|max:50|email',

		];

		$messages = [
			'nome.required' => 'O campo nome é obrigatório.',
			'nome.max' => 'Maximo de 30 caracteres',
			'sobre_nome.required' => 'O campo sobre nome é obrigatório.',
			'sobre_nome.max' => 'Maximo de 30 caracteres',
			'celular.required' => 'O campo celular é obrigatório.',
			'celular.min' => 'Minimo de 15 caracteres',
			'celular.max' => 'Maximo de 15 caracteres',
			'email.required' => 'O campo email é obrigatório.',
			'email.max' => 'Maximo de 50 caracteres',
			'email.email' => 'Email inválido'
		];
		$this->validate($request, $rules, $messages);
	}

	private function _validateEnd(Request $request){
		$rules = [
			'rua' => 'required|max:50',
			'numero' => 'required|max:10',
			'bairro' => 'required|max:30',
			'referencia' => 'required|max:30',
		];

		$messages = [
			'rua.required' => 'O campo rua é obrigatório.',
			'rua.max' => 'Maximo de 50 caracteres',
			'numero.required' => 'O campo numero é obrigatório.',
			'numero.max' => 'Maximo de 10 caracteres',
			'bairro.required' => 'O campo bairro é obrigatório.',
			'bairro.max' => 'Maximo de 30 caracteres',
			'referencia.required' => 'O campo referencia é obrigatório.',
			'referencia.max' => 'Maximo de 30 caracteres',

		];
		$this->validate($request, $rules, $messages);
	}
}
