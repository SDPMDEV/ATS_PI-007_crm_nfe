<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pedido;
use App\ItemPedido;
use App\ItemPizzaPedidoLocal;
use App\Produto;
use App\ProdutoPizza;
use App\ItemPedidoComplementoLocal;
use App\ComplementoDelivery;
use App\VendaCaixa;
use App\ConfigNota;
use Comtele\Services\TextMessageService;
use NFePHP\DA\NFe\CupomPedido;



class PedidoController extends Controller{

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
   $pedidos = Pedido::
   where('desativado', false)
   ->get();

   return view('pedido/list')
   ->with('pedidos', $pedidos)
   ->with('title', 'Lista de Pedidos');
 }


 public function abrir(Request $request){
  $comanda = Pedido::
  where('comanda', $request->comanda)
  ->where('desativado', false)
  ->first();
  if(empty($comanda)){
   $res = Pedido::create([
    'comanda' => $request->comanda,
    'observacao' => $request->observacao ?? '',
    'status' => false,
    'desativado' => false
  ]);
   if($res) {
    session()->flash('color', 'green');
    session()->flash('message', 'Comanda aberta com sucesso!');
  }
}else{
  session()->flash('color', 'red');
  session()->flash('message', 'Esta comanda encontra-se ativa!');
}
return redirect('/pedidos');
}


public function ver($id){
 $pedido = Pedido::
 where('id', $id)
 ->first();

 return view('pedido/ver')
 ->with('pedido', $pedido)
 ->with('pedidoJs', true)
 ->with('title', 'Comanda '.$id);
}

public function alterarStatus($id){
  $item = ItemPedido::
  where('id', $id)
  ->first();

  $item->status = 1;
  $item->save();

  session()->flash('color', 'green');
  session()->flash('message', 'Produto '. $item->produto->nome . ' marcado como concluido!');
  return redirect("/pedidos/ver/".$item->pedido->id);
}

public function deleteItem($id){
 $item = ItemPedido::
 where('id', $id)
 ->first();

 if($item->delete()){
   session()->flash('color', 'green');
   session()->flash('message', 'Item removido!');
 }else{
   session()->flash('color', 'red');
   session()->flash('message', 'Erro');
 }
 return redirect('/pedidos/ver/'.$item->pedido_id);
}

public function desativar($id){
  $item = Pedido::
  where('id', $id)
  ->first();
  $item->desativado = true;
  $res = $item->save();

  if($res){
    session()->flash('color', 'green');
    session()->flash('message', 'Comanda desativada!');
  }else{
    session()->flash('color', 'red');
    session()->flash('message', 'Erro');
  }
  return redirect('/pedidos');
}

public function emAberto(){
  $pedidos = ItemPedido::where('status', false)
  ->get();

  return response()->json(count($pedidos), 200);
}


public function saveItem(Request $request){

  $this->_validateItem($request);
  $pedido = Pedido::
  where('id', $request->id)
  ->first();

  $produto = $request->input('produto');
  $produto = explode("-", $produto);
  $produto = $produto[0];

  $result = ItemPedido::create([
    'pedido_id' => $pedido->id,
    'produto_id' => $produto,
    'quantidade' => str_replace(",", ".", $request->quantidade),
    'status' => false,
    'tamanho_pizza_id' => $request->tamanho_pizza_id ?? NULL,
    'observacao' => $request->observacao ?? '',
    'valor' => str_replace(",", ".", $request->valor)
  ]);

  if($request->tamanho_pizza_id && $request->sabores_escolhidos){
    $saborDup = false;

    $sabores = explode(",", $request->sabores_escolhidos);
    if(count($sabores) > 0){
      foreach($sabores as $sab){
        $prod = Produto
        ::where('nome', $sab)
        ->first();

        $item = ItemPizzaPedidoLocal::create([
          'item_pedido' => $result->id,
          'sabor_id' => $prod->delivery->id,
        ]);

        if($prod->id == $produto) $saborDup = true;
      }
    }
    if(!$saborDup){
      $prod = Produto
      ::where('id', $produto)
      ->first();
      $item = ItemPizzaPedidoLocal::create([
        'item_pedido' => $result->id,
        'sabor_id' => $prod->delivery->id,
      ]);
    }
  }else if($request->tamanho_pizza_id){
    $prod = Produto
    ::where('id', $produto)
    ->first();
    $item = ItemPizzaPedidoLocal::create([
      'item_pedido' => $result->id,
      'sabor_id' => $prod->delivery->id,
    ]);
  }

  if($request->adicioanis_escolhidos){
    $adicionais = explode(",", $request->adicioanis_escolhidos);
    foreach($adicionais as $ad){
      $nome = explode("-", $ad) ;

      $adicional = ComplementoDelivery
      ::where('nome', $nome)
      ->first();


      $item = ItemPedidoComplementoLocal::create([
        'item_pedido' => $result->id,
        'complemento_id' => $adicional->id,
        'quantidade' => str_replace(",", ".", $request->quantidade),
      ]);
    }
  }


  if($result){
   session()->flash('color', 'green');
   session()->flash('message', 'Item adicionado!');
 }else{
   session()->flash('color', 'red');
   session()->flash('message', 'Erro');
 }
 return redirect('/pedidos/ver/'.$pedido->id);
}

private function _validateItem(Request $request){
  $validaTamanho = false;
  if($request->input('produto')){
    $produto = $request->input('produto');
    $produto = explode("-", $produto);
    $produto = $produto[0];

    $p = Produto::
    where('id', $produto)
    ->first();

    if(strpos($p->categoria->nome, 'izza') !== false){
      $validaTamanho = true;
    }
  }
  $rules = [
    'produto' => 'required|min:5',
    'quantidade' => 'required',
    'tamanho_pizza_id' => $validaTamanho ? 'required' : '',
  ];

  $messages = [
    'produto.required' => 'O campo produto é obrigatório.',
    'produto.min' => 'Clique sobre o produto desejado.',
    'quantidade.required' => 'O campo quantidade é obrigatório.',
    'tamanho_pizza_id.required' => 'Selecione um tamanho.',
  ];

  $this->validate($request, $rules, $messages);
}

public function finalizar($id){
 $pedido = Pedido::
 where('id', $id)
 ->first();

 $atributes = $this->addAtributes($pedido->itens);
 $pedido->status = 1;
 $pedido->desativado = 1;
 $pedido->save();

 $tiposPagamento = VendaCaixa::tiposPagamento();
 $config = ConfigNota::first();
 return view('frontBox/main')
 ->with('itens', $atributes)
 ->with('frenteCaixa', true)
 ->with('tiposPagamento', $tiposPagamento)
 ->with('config', $config)
 ->with('title', 'Finalizar Comanda '.$id);
}


private function addAtributes($itens){
  $temp = [];
  foreach($itens as $i){
    $i->produto;

    if(!empty($i->sabores)){
      $i->sabores;

      $valorAdicional = 0;

      foreach($i->itensAdicionais as $ad){
        $valorAdicional += $ad->adicional->valor;
      }

      $i->valorAdicional = $valorAdicional;

      $maiorValor = 0;
      foreach($i->sabores as $sb){
        $sb->produto->produto;
        $v = $sb->maiorValor($sb->sabor_id, $i->tamanho_pizza_id);

        if($v > $maiorValor) $maiorValor = $v;
      }
      $i->maiorValor = $maiorValor;
    }
    $i->produto->valor_venda = $i->valor;
    $i->produto_id = $i->produto->id;
    $i->produto->nome = $i->produto->nome;
    $i->item_pedido = $i->id;
    array_push($temp, $i);
  }
        // echo json_encode($temp);
  return $temp;
}

public function itensPendentes(){
  $itens = ItemPedido::
  where('status', false)
  ->get();

  echo json_encode(count($itens));
}

public function sms(Request $request){
  $data = $request->data;
  $phone = $data['numero'];
  $msg = $data['msg'];
  $res = $this->sendSms($phone, $msg);
  echo json_encode($res);
}

private function sendSms($phone, $msg){
  $nomeEmpresa = getenv('SMS_NOME_EMPRESA');
  $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
  $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
  $content = $msg;
  $textMessageService = new TextMessageService(getenv('SMS_KEY'));
  $res = $textMessageService->send("Sender", $content, [$phone]);
  return $res;
}

public function imprimirPedido($id){
  $pedido = Pedido::
  where('id', $id)
  ->first();

  $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
  $pathLogo = $public.'imgs/logo.jpg';

  $cupom = new CupomPedido($pedido, $pathLogo);
  $cupom->monta();
  $pdf = $cupom->render();

  header('Content-Type: application/pdf');
  echo $pdf;
}

public function itensParaFrenteCaixa(Request $request){
  $cod = $request->cod;

  $pedido = Pedido::
  where('comanda', $cod)
  ->where('status', 0)
  ->where('desativado', 0)
  ->first();

  if($pedido == null) return response()->json("Nao existe", 401);

  $atributes = $this->addAtributes($pedido->itens);
  return response()->json($atributes, 200);
}

}
