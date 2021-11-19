<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\StockMove;
use App\Estoque;
use App\Produto;
use App\Apontamento;
use App\AlteracaoEstoque;

class StockController extends Controller
{
	public function __construct(){
        $this->middleware(function ($request, $next) {
            $value = session('user_logged');
            if(!$value){
                return redirect("/login");
            }else{
                if($value['acesso_estoque'] == 0){
                    return redirect("/sempermissao");
                }
            }
            return $next($request);
        });
    }

    public function index(){
      $estoque = Estoque::
      orderBy('updated_at', 'desc')
      ->paginate(20);
      return view('stock/list')
      ->with('estoque', $estoque)
      ->with('links', true)
      ->with('title', 'Estoque');
  }



  public function apontamento(){
    $apontamentos = Apontamento::limit(5)
    ->orderBy('id', 'desc')
    ->get();

    $produtos = Produto::where('composto', 1)->get();
    return view('stock/apontamento')
    ->with('apontamentos', $apontamentos)
    ->with('produtos', $produtos)
    ->with('produtoJs', true)
    ->with('title', 'Apontamento');
}

public function apontamentoManual(){
    $produtos = Produto::all();
    return view('stock/apontaManual')
    ->with('produtoJs', false)
    ->with('produtos', $produtos)
    ->with('title', 'Apontamento Manual');
}

public function todosApontamentos(){
    $apontamentos = Apontamento::
    orderBy('id', 'desc')
    ->paginate(10);
    return view("stock/todosApontamentos")
    ->with('apontamentos', $apontamentos)
    ->with('links', true)
    ->with('title', 'Todos os apontamentos');
}

public function filtroApontamentos(Request $request){
    $apontamentos = Apontamento::
    whereBetween('data_registro', 
        [$this->parseDate($request->dataInicial), 
            $this->parseDate($request->dataFinal)])
    ->orderBy('data_registro', 'desc')
    ->get();
    return view("stock/todosApontamentos")
    ->with('apontamentos', $apontamentos)
    ->with('dataInicial', $request->dataInicial)
    ->with('dataFinal', $request->dataFinal)
    ->with('title', 'Todos os apontamentos');
}

private function parseDate($date){
    return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
}

public function saveApontamento(Request $request){
    $this->_validateApontamento($request);

    $produto = $request->input('produto');
    $produto = explode("-", $produto);
    $produto = $produto[0];

    $result = Apontamento::create([
        'quantidade' => str_replace(",", ".", $request->quantidade),
        'usuario_id' => get_id_user(),
        'produto_id' => $produto
    ]);

    $prod = Produto::
    where('id', $produto)
    ->first();

    $stockMove = new StockMove();
    $stockMove->pluStock((int) $produto, 
        str_replace(",", ".", $request->quantidade),
        str_replace(",", ".", $prod->valor_venda));

    $this->downEstoquePorReceita($produto, str_replace(",", ".", $request->quantidade));

    if($result){
        session()->flash("mensagem_sucesso", "Apontamento cadastrado com sucesso!");
    }else{
        session()->flash('mensagem_erro', 'Erro ao cadastrar apontamento!');
    }

    return redirect("/estoque/apontamentoProducao");
}


public function saveApontamentoManual(Request $request){
    $this->_validateApontamento($request);

    $produto = $request->input('produto');
    $prod = Produto::
    where('id', $produto)
    ->first();

    $dataAlteracao = [
        'produto_id' => $produto,
        'usuario_id' => get_id_user(),
        'quantidade' => $request->quantidade,
        'tipo' => $request->tipo,
        'observacao' => $request->observacao ?? ''
    ];


    AlteracaoEstoque::create($dataAlteracao);

    $stockMove = new StockMove();
    $result = null;
    if($request->tipo == 'incremento'){
        $result = $stockMove->pluStock((int) $produto, 
            $request->quantidade,
            str_replace(",", ".", $prod->valor_venda));
    }else{
        // echo $produto;
        $result = $stockMove->downStock((int)$produto, $request->quantidade);
    }

    if($result){
        session()->flash("mensagem_sucesso", "Apontamento Manual cadastrado com sucesso!");
    }else{
        session()->flash('mensagem_erro', 'Erro ao cadastrar apontamento manual!');
    }

    return redirect("/estoque");
}

private function downEstoquePorReceita($idProduto, $quantidade){
    $produto = Produto::
    where('id', $idProduto)
    ->first();
    $stockMove = new StockMove();
    foreach($produto->receita->itens as $i){
        $stockMove->downStock($i->produto->id, $i->quantidade * $quantidade);
    }

}

// public function deleteApontamento($id){
//     $ap = Apontamento::
//     where('id', $id)
//     ->first();

//     $stockMove = new StockMove();
//     foreach($ap->produto->receita->itens as $i){
//         echo $i->quantidade;
//         $stockMove->downStock($i->produto->id, $i->quantidade * $quantidade);
//     }
// }

private function _validateApontamento(Request $request){
    $rules = [
        'produto' => 'required',
        'quantidade' => 'required|min:4',
    ];

    $messages = [
        'produto.required' => 'O campo produto é obrigatório.',
        'produto.min' => 'Clique sobre o produto desejado.',
        'quantidade.required' => 'O campo quantidade é obrigatório.',
        'quantidade.min' => 'Informe o valor do campo em casas decimais, ex: 1,000.'
    ];

    $this->validate($request, $rules, $messages);

}

public function listApontamentos(){
    $apontamentos = AlteracaoEstoque::orderBy('id', 'desc')->get();

    return view('stock/listaAlteracao')
    ->with('title', 'Lista de Alterações')
    ->with('apontamentos', $apontamentos);
}

public function listApontamentosDelte($id){
    $alteracao = AlteracaoEstoque::find($id);

    $stockMove = new StockMove();

    if($alteracao->tipo != 'incremento'){
        $result = $stockMove->pluStock($alteracao->produto_id, $alteracao->quantidade);
    }else{
        $result = $stockMove->downStock($alteracao->produto_id, $alteracao->quantidade);
    }

    $alteracao->delete();

    session()->flash('mensagem_sucesso', 'Registro removido!');
    return redirect("/estoque/listApontamentos");

}

}
