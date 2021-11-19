<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pedido;
use App\ItemPedido;
use App\ItemPedidoDelivery;
use App\TelaPedido;

class CozinhaController extends Controller
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
    
    public function index($id = NULL){

        $tela = 'Todos';
        if($id != null){
            $tela = TelaPedido::find($id)->nome;
        }

        return view('controleCozinha/index')
        ->with('cozinhaJs', true)
        ->with('id', $id)
        ->with('tela', $tela)
        ->with('title', 'Controle de Pedidos');
    }

    public function buscar(Request $request){
    	$itens = ItemPedido::
    	where('status', false)
    	->orderBy('created_at', 'asc')
    	->get();


        $itensDelivery = ItemPedidoDelivery::
        where('status', false)
        ->orderBy('created_at', 'asc')
        ->get();

        $tela = $request->tela;
        $tipoTela = null;
        if($tela > 0){
            $tipoTela = TelaPedido::find($tela);
        }

        $arr = [];
        foreach($itens as $i){
            $pTemp = $i->produto;
            $i->produto;
            $i->comanda = $i->pedido->comanda;


            $adicionais = "";
            foreach($i->itensAdicionais as $key => $a){

                $adicionais .= $a->adicional->nome . ($key < count($i->itensAdicionais)-1 ? " | " : "");
            }

            $saboresPizza = "";

            foreach($i->sabores as $key => $s){
                $saboresPizza .= $s->produto->produto->nome . ($key < count($i->sabores)-1 ? " | " : "");
            }

            $i->tamanhoPizza = $i->tamanho != null ? $i->tamanho->nome() : false;


            $i->adicionais = $adicionais;
            $i->saboresPizza = $saboresPizza;
            $i->data = \Carbon\Carbon::parse($i->created_at)->format('d/m H:i');

            $dataPedido = \Carbon\Carbon::parse($i->created_at)->format('Y-m-d H:i:s');
            $dataAgora = date('Y-m-d H:i:s');

            $date1 = strtotime($dataPedido);
            $date2 = strtotime($dataAgora);

            $dif = (int)($date2 - $date1)/60;
            $i->cor = "white";
            if($dif > $tipoTela->alerta_amarelo){
                $i->cor = 'yellow';
            }

            if($dif > $tipoTela->alerta_vermelho){
                $i->cor = 'red';
            }

            $i->teste = $dif;

            if($tela == 0 || $pTemp->tela_id == $tela){
                array_push($arr, $i);
            }
        }

        foreach($itensDelivery as $i){

            if($i->pedido->estado == 'ap'){
                $pTemp = $i->produto->produto;

                $i->produto->produto;
                $i->comanda = null;

                $adicionais = "";
                foreach($i->itensAdicionais as $key => $a){

                    $adicionais .= $a->adicional->nome . ($key < count($i->itensAdicionais)-1 ? " | " : "");
                }

                $saboresPizza = "";

                foreach($i->sabores as $key => $s){
                    $saboresPizza .= $s->produto->produto->nome . ($key < count($i->sabores)-1 ? " | " : "");
                }

                $i->tamanhoPizza = $i->tamanho != null ? $i->tamanho->nome() : false;


                $i->adicionais = $adicionais;
                $i->saboresPizza = $saboresPizza;
                $i->data = \Carbon\Carbon::parse($i->created_at)->format('d/m H:i');
                if($tela == 0 || $pTemp->tela_id == $tela){
                    array_push($arr, $i);
                }
            }
        }
        usort($arr, function($a, $b){
            return strcmp($b->created_at, $a->created_at);
        });
        return response()->json($arr, 200);
    }

    public function concluido(Request $request){
        $ehDelivery = $request->ehDelivery;

        if($ehDelivery == 1){
            $item = ItemPedidoDelivery::find($request->id);
            $item->status = true;

            return response()->json($item->save(), 200);
        }else{
            $item = ItemPedido::find($request->id);
            $item->status = true;

            return response()->json($item->save(), 200);
        }

    }

    public function selecionar(){
        $telas = TelaPedido::all();
        if(sizeof($telas) > 0){
            return view('controleCozinha/selecionar')
            ->with('telas', $telas)
            ->with('title', 'Tipo de controle');
        }else{
            return redirect('/controleCozinha/controle');
        }
    }
}
