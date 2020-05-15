<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PedidoDelivery;
use App\ItemPedidoDelivery;
use App\DeliveryConfig;
use App\ClienteDelivery;
use NFePHP\DA\NFe\PedidoPrint;
use App\VendaCaixa;
use App\ConfigNota;

class PedidoDeliveryController extends Controller
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

	public function today(){
		
		$pedidosNovo = $this->filtroPedidos(date("Y-m-d"),
			date('Y-m-d', strtotime('+1 day')), 'nv');

		$pedidosAprovado = $this->filtroPedidos(date("Y-m-d"),
			date('Y-m-d', strtotime('+1 day')), 'ap');

		$pedidosRecusado = $this->filtroPedidos(date("Y-m-d"),
			date('Y-m-d', strtotime('+1 day')), 'rc');

		$pedidosReprovaco = $this->filtroPedidos(date("Y-m-d"),
			date('Y-m-d', strtotime('+1 day')), 'rp');

		$pedidosFinalizado = $this->filtroPedidos(date("Y-m-d"),
			date('Y-m-d', strtotime('+1 day')), 'fz');

		$carrinho = $this->filtroPedidos(date("Y-m-d"),
			date('Y-m-d', strtotime('+1 day')), 'nv', '=');


		return view('pedidosDelivery/list')
		->with('tipo', 'Pedidos de Hoje')
		->with('pedidosNovo', $pedidosNovo)
		->with('pedidosAprovado', $pedidosAprovado)
		->with('pedidosRecusado', $pedidosRecusado)
		->with('pedidosReprovaco', $pedidosReprovaco)
		->with('pedidosFinalizado', $pedidosFinalizado)
		->with('carrinho', $carrinho)
		->with('somaNovos', $this->somaPedidos($pedidosNovo))
		->with('somaAprovados', $this->somaPedidos($pedidosAprovado))
		->with('somaRecusados', $this->somaPedidos($pedidosRecusado))
		->with('somaReprovados', $this->somaPedidos($pedidosReprovaco))
		->with('somaFinalizados', $this->somaPedidos($pedidosFinalizado))
		->with('carrinho', $this->somaCarrinho($carrinho))
		->with('title', 'Pedidos de Delivery');

	}

	private function somaPedidos($arr){
		$v = 0;
		foreach($arr as $r){

			$v += $r->somaItens();
		}
		return $v;
	}

	private function somaCarrinho($arr){
		$v = 0;
		foreach($arr as $r){
			$v += $r->somaCarrinho();
		}
		return $v;
	}

	public function filtro(Request $request){
		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;

		$pedidosNovo = $this->filtroPedidos($this->parseDate($dataInicial),
			$this->parseDate($dataFinal, true), 'nv');

		$pedidosAprovado = $this->filtroPedidos($this->parseDate($dataInicial),
			$this->parseDate($dataFinal, true), 'ap');

		$pedidosRecusado = $this->filtroPedidos($this->parseDate($dataInicial),
			$this->parseDate($dataFinal, true), 'rc');

		$pedidosReprovaco = $this->filtroPedidos($this->parseDate($dataInicial),
			$this->parseDate($dataFinal, true), 'rp');

		$pedidosFinalizado = $this->filtroPedidos($this->parseDate($dataInicial),
			$this->parseDate($dataFinal, true), 'fz');

		$carrinho = $this->filtroPedidos($this->parseDate($dataInicial),
			$this->parseDate($dataFinal, true), 'nv', '=');

		return view('pedidosDelivery/list')
		->with('tipo', 'Pedidos de Hoje')
		->with('pedidosNovo', $pedidosNovo)
		->with('pedidosAprovado', $pedidosAprovado)
		->with('pedidosRecusado', $pedidosRecusado)
		->with('pedidosReprovaco', $pedidosReprovaco)
		->with('pedidosFinalizado', $pedidosFinalizado)
		->with('somaNovos', $this->somaPedidos($pedidosNovo))
		->with('somaAprovados', $this->somaPedidos($pedidosAprovado))
		->with('somaRecusados', $this->somaPedidos($pedidosRecusado))
		->with('somaReprovados', $this->somaPedidos($pedidosReprovaco))
		->with('somaFinalizados', $this->somaPedidos($pedidosFinalizado))
		->with('carrinho', $this->somaCarrinho($carrinho))
		->with('dataInicial', $dataInicial)
		->with('dataFinal', $dataFinal)
		->with('title', 'Pedidos de Delivery periodo '. $dataInicial . ' até ' . $dataFinal);
	}

	public function verCarrinhos(){
		$pedidos = PedidoDelivery::
		where('estado', 'nv')
		->where('valor_total', 0)
		->get();

		return view('pedidosDelivery/carrinhos')
		->with('pedidos', $pedidos)
		->with('title', 'Carrinhos em Aberto');
	}

	public function verCarrinho($id){
		$pedido = PedidoDelivery::
		where('id', $id)
		->first();

		return view('pedidosDelivery/verCarrinho')
		->with('pedido', $pedido)
		->with('title', 'Itens do Carrinho Aberto');
	}

	public function push($id){
		$pedido = PedidoDelivery::
		where('id', $id)
		->first();

		return view('push/new')
		->with('pushJs', true)
		->with('titulo', $this->randomTitles())
		->with('mensagem', $this->randomMensagem($pedido))
		->with('title', 'Nova Push');
	}

	private function randomTitles(){
		$titles = [
			'Fecha seu carrinho conosco',
			'Vamos finaizar o pedido',
			'Não perca isso',
			'Não deixe de finalizar'
		];
		return $titles[rand(0,3)];
	}

	private function randomMensagem($pedido){
		$messages = [
			'Seu carrinho esta em, R$ '. number_format($pedido->somaItens(), 2),
			'Vamos fechar este pedido preparamos um desconto para você',
			'Finalize já este carrinho conosco, preparamos o melhor para você :)',
		];
		return $messages[rand(0,2)];
	}

	public function verPedido($id){
		$pedido = PedidoDelivery
		::where('id', $id)
		->first();

		return view('pedidosDelivery/detalhe')
		->with('tipo', 'Detalhes do Pedido')
		->with('pedido', $pedido)
		->with('pedidoDeliveryJs', true)
		->with('title', 'Pedidos de Delivery');
	}

	public function alterarStatus($id){
		$item = ItemPedidoDelivery
		::where('id', $id)
		->first();

		$item->status = true;
		$item->save();
		return redirect("/pedidosDelivery/verPedido/".$item->pedido->id);
	}

	public function alterarPedido(Request $request){
		$id = $request->id;
		$tipo = $request->tipo;

		$pedido = PedidoDelivery
		::where('id', $id)
		->first();

		return view('pedidosDelivery/alterarEstado')
		->with('tipo', 'Detalhes do Pedido')
		->with('pedido', $pedido)
		->with('tipo', $tipo)
		->with('title', 'Pedidos de Delivery');
	}

	public function emAberto(){
		$pedidos = PedidoDelivery::
		where('estado', 'nv')
		->where('valor_total', '>', 0)
		->get();

		return response()->json(count($pedidos), 200);
	}

	public function confirmarAlteracao(Request $request){
		$config = ConfigNota::first();

		if($config == null){
			session()->flash('color', 'red');
			session()->flash('message', 'Defina a configuração do emitente para continuar!');
			return redirect()->back();
		}

		$id = $request->id;
		$tipo = $request->tipo;

		$pedido = PedidoDelivery
		::where('id', $id)
		->first();

		$pedido->estado = $tipo;
		$pedido->motivoEstado = $request->motivoEstado ?? '';

		$pedido->save();

		if($tipo == 'fz'){
			//Abrir frente de caixa

			$tiposPagamento = VendaCaixa::tiposPagamento();
			return view('frontBox/main')
			->with('itens', $this->addAtributes($pedido->itens))
			->with('frenteCaixa', true)
			->with('delivery_id', $pedido->id)
			->with('tiposPagamento', $tiposPagamento)
			->with('config', $config)
			->with('title', 'Finalizar Comanda '.$id);
		}else{
			session()->flash('color', 'green');
			session()->flash('message', 'Pedido Alterado!');
			return redirect('/pedidosDelivery');
		}

	}

	private function addAtributes($itens){
		$temp = [];
		foreach($itens as $i){
			$i->produto;

			$valorAdicional = 0;

			foreach($i->itensAdicionais as $ad){
				$valorAdicional += $ad->adicional->valor;
			}

			$i->valorAdicional = $valorAdicional;

			if(count($i->sabores) > 0){
				$i->sabores;

				$maiorValor = 0;
				foreach($i->sabores as $sb){
					$sb->produto->produto;
					$v = $sb->maiorValor($sb->sabor_id, $i->tamanho_id);
					if($v > $maiorValor) $maiorValor = $v;
				}
				$i->maiorValor = $maiorValor;
			}else{
				$i->produto->valor_venda = $i->produto->valor;
			}
			$i->produto_id = $i->produto->produto->id; // converte id de delivery para produto comum
			$i->produto->nome = $i->produto->produto->nome;
			array_push($temp, $i);
		}
    	// echo json_encode($temp);
		return $temp;
	}

	public function irParaFrenteCaixa($id){

		$pedido = PedidoDelivery
		::where('id', $id)
		->first();

		$config = ConfigNota::first();
		$tiposPagamento = VendaCaixa::tiposPagamento();

		// if($pedido)
		return view('frontBox/main')
		->with('itens', $this->addAtributes($pedido->itens))
		->with('frenteCaixa', true)
		->with('tiposPagamento', $tiposPagamento)
		->with('config', $config)
		->with('title', 'Finalizar Comanda '.$id);
		
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	private function filtroPedidos($dataInicial, $dataFinal, $estado, $sinal = '>'){
		$pedidos = PedidoDelivery::
		whereBetween('data_registro', [$dataInicial, 
			$dataFinal])
		->where('estado', $estado)
		->where('valor_total', $sinal, 0)
		->get();
		return $pedidos;
	}

	public function print($id){
		$pedido = PedidoDelivery::
		where('id', $id)
		->first();

		$ped = new PedidoPrint($pedido);
		$ped->monta();
		$pdf = $ped->render();

		header('Content-Type: application/pdf');
		echo $pdf;
	}

	public function sendPush(Request $request){
		$cliente = ClienteDelivery::where('id', $request->cliente)
		->first();
		$tkTemp = [];
		if(count($cliente->tokens) > 0){
			foreach($cliente->tokens as $t){
				if(!in_array($t->token, $tkTemp)){

					array_push($tkTemp, $t->user_id);
				}
			}

			$data = [
				'heading' => [
					"en" => $request->titulo
				],
				'content' => [
					"en" => $request->texto
				],
				'image' => $request->imagem ?? '',
				'referencia_produto' => 0,
			];

			$this->sendMessageOneSignal($data, $tkTemp);
		}
		echo json_encode('sucesso');

	}

	public function sendMessageOneSignal($data, $tokens = null){

		$fields = [
			'app_id' => getenv('ONE_SIGNAL_APP_ID'),
			'contents' => $data['content'],
			'headings' => $data['heading'],
			'large_icon' => getenv('PATH_URL').'/imgs/logo.png',
			'small_icon' => 'notification_icon'
		];

		if($data['image'] != '')
			$fields['big_picture'] = $data['image'];

		if($tokens == null){
			$fields['included_segments'] = array('All');
			if($data['image'] != '')
			$fields['chrome_web_image'] = $data['image'];
		}else{
			$fields['include_player_ids'] = $tokens;
		}


		if($data['referencia_produto'] > 0){
			$fields['web_url'] = getenv('PATH_URL') . "/cardapio/verProduto/" . $data['referencia_produto'];
			$produtoDelivery = ProdutoDelivery::find($data['referencia_produto']);
			if($produtoDelivery != null){
				$produtoDelivery->pizza;
				$produtoDelivery->galeria;
				$produtoDelivery->categoria;
				$produtoDelivery->produto;
				$fields['data'] = ["referencia" => $produtoDelivery];
			}
		}

		$fields = json_encode($fields);
		print("\nJSON sent:\n");
		print($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
			'Authorization: Basic '.getenv('ONE_SIGNAL_KEY')));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
}
