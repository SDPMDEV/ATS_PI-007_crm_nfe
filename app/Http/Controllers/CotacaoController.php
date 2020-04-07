<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Fornecedor;
use App\Cotacao;
use App\ItemCotacao;
use Mail;

class CotacaoController extends Controller
{
	public function index(){

		date_default_timezone_set('America/Sao_Paulo');
		$dataCurrent = date("Y-m-d");
		$dataBefore = date('Y-m-d', strtotime($dataCurrent. ' - 15 days'));

		$cotacoes = Cotacao::
		whereRaw("DATE_FORMAT(data_registro, '%Y-%m-%d') BETWEEN '$dataBefore' AND '$dataCurrent'")
		->orderBy('id', 'desc')
		->get();


		return view('cotacao/list')
		->with('cotacoes', $cotacoes)
		->with('title', 'Cotações');

	}

	public function clonar($id){

		$cotacao = Cotacao::
		where('id', $id)
		->first();

		return view('cotacao/clone')
		->with('cotacaoJs', true)
		->with('cloneJs', true)
		->with('cotacao', $cotacao)
		->with('title', 'Clonar Cotação');


	}

	public function clonarSave(Request $request){
		$data = $request->data;
		$cotacao = Cotacao
		::where('id', $data['cotacao'])
		->first();
		$tst = 0;
		foreach($data['fornecedores'] as $f){
			$forn = Fornecedor::
			where('id', $f)
			->first();

			$result = Cotacao::create([ 
				'forma_pagamento' => '*',
				'responsavel' => '',
				'valor' => 0,
				'desconto' => 0,
				'fornecedor_id' => $forn->id,
				'link' => $this->generateRandomString(20),
				'referencia' => $cotacao->referencia,
				'observacao' => $cotacao->observacao,
				'resposta' => false,
				'ativa' => true

			]);


			foreach($cotacao->itens as $i){
				$itemResult = ItemCotacao::create([ 
					'cotacao_id' => $result->id,
					'produto_id' => $i->produto->id,
					'valor' => 0,
					'quantidade' => $i->quantidade
				]);
			}


		}
		echo json_encode($cotacao);
	}



	public function searchProvider(Request $request){
		if($this->logged){
			$providers = Provider::
			all();

			$vehicles = Vehicle::
			all();

			$quotes = Price::
			where('provider_id', $request->input('provider_id'))
			->orderBy('id', 'desc')
			->get();

			return view('quotes/list')
			->with('providers', $providers)
			->with('quotes', $quotes)
			->with('vehicles', $vehicles)
			->with('title', 'Cotações');
		}else{
			return redirect("/login");
		}
	}

	public function searchPiece(Request $request){
		if($this->logged){
			$providers = Provider::
			all();

			$vehicles = Vehicle::
			all();
			$search = $request->input('search');

			date_default_timezone_set('America/Sao_Paulo');
			$quotes = Price::
            //whereRaw("DATE_FORMAT(date_register, '%Y-%m-%d') BETWEEN '$dataBefore' AND '$dataCurrent'")
			selectRaw('prices.*')
			->join('piece_prices', 'piece_prices.price_id', '=', 'prices.id')
			->join('pieces', 'pieces.id', '=', 'piece_prices.piece_id')
			->where('pieces.description', 'like', "%$search%")
			->get();

			return view('quotes/list')
			->with('providers', $providers)
			->with('quotes', $quotes)
			->with('vehicles', $vehicles)
			->with('title', 'Cotações');
		}else{
			return redirect("/login");
		}
	}

	public function new(){

		return view('cotacao/register')
		->with('cotacaoJs', true)
		->with('title', 'Nova Cotação');
	}

	public function salvar(Request $request){
		$cotacao = $request->cotacao;
		$result = Cotacao::create([ 
			'forma_pagamento' => '*',
			'responsavel' => '',
			'valor' => 0,
			'desconto' => 0,
			'fornecedor_id' => $cotacao['fornecedor'],
			'link' => $this->generateRandomString(20),
			'referencia' => $cotacao['referencia'] ?? '',
			'observacao' => $cotacao['observacao'] ?? '',
			'resposta' => false,
			'ativa' => true

		]);

		$itens = $cotacao['itens'];

		foreach($itens as $i){
			$itemResult = ItemCotacao::create([ 
				'cotacao_id' => $result->id,
				'produto_id' => (int)$i['codigo'],
				'valor' => 0,
				'quantidade' => str_replace(",", ".", $i['quantidade'])
			]);
		}

		echo json_encode($result);

	}

	private function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

    // PEÇAS DA COTAÇÂO
	public function edit($id){
		$cotacao = Cotacao::
		where('id', $id)
		->first();

		return view('cotacao/edit')
		->with('cotacao', $cotacao)
		->with('cotacaoJs', true)
		->with('title', 'Editar Cotação');

		echo json_encode($cotacao);
	}



	public function deleteItem($id){
		$item = ItemCotacao::
		where('id', $id)
		->first();

		$cotacaoId = $item->cotacao->id;

		if($item->delete()){
			session()->flash('color', 'green');
			session()->flash('message', 'Cotação removida!');
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro');
		}


		return redirect("/cotacao/edit/$cotacaoId");

	}

	public function saveItem(Request $request){

		$cotacao = Cotacao::
		where('id', $request->id)
		->first();

		$produto = $request->input('produto');
		$produto = explode("-", $produto);
		$produto = $produto[0];

		$result = ItemCotacao::create([ 
			'cotacao_id' => $cotacao->id,
			'produto_id' => (int)$produto,
			'valor' => 0,
			'quantidade' => str_replace(",", ".", $request->quantidade)
		]);

		if($result){
			session()->flash('color', 'green');
			session()->flash('message', 'Produto adicionado!');
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro');
		}


		return redirect("/cotacao/edit/$cotacao->id");

	}

	public function delete($id){

		if(Cotacao::where('id', $id)->delete()){
			session()->flash('color', 'green');
			session()->flash('message', 'Cotação removida!');
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro');
		}
		return redirect("/cotacao");

	}

	public function alterarStatus($id, $status){

		$cotacao = Cotacao::
		where('id', $id)
		->first();

		$cotacao->ativa = $status;
		$result = $cotacao->save();

		if($result){
			session()->flash('color', 'green');
			session()->flash('message', 'Cotação ' . 
				($status == 1 ? 'Ativada!' : 'Desativada!'));
		}else{
			session()->flash('color', 'red');
			session()->flash('message', 'Erro');
		}
		return redirect("/cotacao");

	}

	public function view($id){

		$cotacao = Cotacao::
		where('id', $id)
		->first();

		return view('cotacao/view')
		->with('cotacao', $cotacao)
		->with('title', 'Cotação');

	}


	public function responseSave(Request $request){
		$js = $request->js;
		$return = true;
		foreach($js as $key => $j){
			$temp = json_decode(json_encode($j));
            if(isset($temp->value)){ // Editar Peças da Cotacao
            	$pieceQuote = PiecePrice::
            	where('id', $temp->id)
            	->first();

            	$pieceQuote->cost = str_replace(",", ".", $temp->value);

            	$pieceQuote->note = $temp->note ?? "";

            	$pieceQuote->save();

            	$piece = Piece::
            	where('id', $pieceQuote->piece_id)
            	->first();

            	$piece->code = $temp->code;

            	if($return)
            		$return = $piece->save();


            }else{ // Editar Cotação
            	$price = Price::
            	where('id', $temp->price_id)
            	->first();

            	$price->cost = $temp->total;
            	$price->responsible = $temp->responsible;
            	$price->payment_form = $temp->payment_form ?? "";
            	$price->response = true;
            	if($return)
            		$return = $price->save();

            }
        }
        echo json_encode($return);
    }

    public function sendMail($id){
    	$cotacao = Cotacao::
    	where('id', $id)
    	->first();
    	$pathUrl = getenv('PATH_URL');

    	Mail::send('mail.cotacao', ['link' => 
    		"$pathUrl/response/$cotacao->link"], 

    		function($message) use ($cotacao){
    			$nomeEmpresa = getenv('SMS_NOME_EMPRESA');
    			$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
    			$nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);

    			$emailEnvio = getenv('MAIL_USERNAME');

    			$message->to($cotacao->fornecedor->email)->subject('Cotação de Serviço/Compra');
    			$message->from($emailEnvio, $nomeEmpresa);
    			$message->subject('Envio de Cotação ' . $cotacao->id);

    		});

    	session()->flash('color', 'green');
    	session()->flash('message', 'EMAIL ENVIADO');

    	return redirect("/cotacao");

    }
}
