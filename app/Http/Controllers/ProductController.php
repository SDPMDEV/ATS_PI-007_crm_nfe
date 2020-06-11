<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produto;
use App\Categoria;
use App\ConfigNota;
use App\Tributacao;
use App\Rules\EAN13;
use App\Helpers\StockMove;


class ProductController extends Controller
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
        $produtos = Produto::
        paginate(15);
        $categorias = Categoria::all();

        return view('produtos/list')
        ->with('produtos', $produtos)
        ->with('links', true)
        ->with('categorias', $categorias)
        ->with('title', 'Produtos');
    }

    public function new(){
        $anps = Produto::lista_ANP();
        $natureza = Produto::firstNatureza();

        if($natureza == null){
            session()->flash('color', 'red');
            session()->flash('message', 'Cadastre uma natureza de operação!');
            return redirect('/naturezaOperacao');
        }

        $categorias = Categoria::all();

        $listaCSTCSOSN = Produto::listaCSTCSOSN();
        $listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
        $listaCST_IPI = Produto::listaCST_IPI();
        $tributacao = Tributacao::first();

        if($tributacao == null){
            session()->flash('color', 'red');
            session()->flash('message', 'Informe a tributação padrão!');
            return redirect('tributos');
        }

        $unidadesDeMedida = Produto::unidadesMedida();
        $config = ConfigNota::first();
        return view('produtos/register')
        ->with('categorias', $categorias)
        ->with('unidadesDeMedida', $unidadesDeMedida)

        ->with('listaCSTCSOSN', $listaCSTCSOSN)
        ->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
        ->with('listaCST_IPI', $listaCST_IPI)
        ->with('anps', $anps)
        ->with('config', $config)
        ->with('tributacao', $tributacao)
        ->with('natureza', $natureza)
        ->with('produtoJs', true)
        ->with('title', 'Cadastrar Produto');
    }

    public function save(Request $request){
        $produto = new Produto();

        $anps = Produto::lista_ANP();
        $descAnp = '';
        foreach($anps as $key => $a){
            if($key == $request->anp){
                $descAnp = $a;
            }
        }
        $request->merge([ 'composto' => $request->input('composto') ? true : false ]);
        $request->merge([ 'valor_livre' => $request->input('valor_livre') ? true : false ]);
        $request->merge([ 'valor_venda' =>str_replace(",", ".", $request->input('valor_venda'))]);
        $request->merge([ 'conversao_unitaria' => $request->input('conversao_unitaria') ? 
            $request->input('conversao_unitaria') : 1]);

        $request->merge([ 'codBarras' => $request->input('codBarras') ?? 'SEM GTIN']);
        $request->merge([ 'CST_CSOSN' => $request->input('CST_CSOSN') ?? '0']);
        $request->merge([ 'CST_PIS' => $request->input('CST_PIS') ?? '0']);
        $request->merge([ 'CST_COFINS' => $request->input('CST_COFINS') ?? '0']);
        $request->merge([ 'CST_IPI' => $request->input('CST_IPI') ?? '0']);
        $request->merge([ 'codigo_anp' => $request->anp != '' ? $request->anp : '']);
        $request->merge([ 'descricao_anp' => $request->anp != '' ? $descAnp : '']);
        $this->_validate($request);

        $result = $produto->create($request->all());

        if($result){
            session()->flash('color', 'blue');
            session()->flash("message", "Produto cadastrado com sucesso!");
        }else{
            session()->flash('color', 'red');
            session()->flash('message', 'Erro ao cadastrar produto!');
        }
        
        return redirect('/produtos');
    }

    public function edit($id){
        $natureza = Produto::firstNatureza();
        $anps = Produto::lista_ANP();

        if($natureza == null){
            session()->flash('color', 'red');
            session()->flash('message', 'Cadastre uma natureza de operação!');
            return redirect('/naturezaOperacao');
        }

        $produto = new Produto(); 

        $listaCSTCSOSN = Produto::listaCSTCSOSN();
        $listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
        $listaCST_IPI = Produto::listaCST_IPI();

        $categorias = Categoria::all();
        $unidadesDeMedida = Produto::unidadesMedida();
        $config = ConfigNota::first();
        $tributacao = Tributacao::first();
        $resp = $produto
        ->where('id', $id)->first();  

        if($tributacao == null){
            session()->flash('color', 'red');
            session()->flash('message', 'Informe a tributação padrão!');
            return redirect('tributos');
        }

        return view('produtos/register')
        ->with('produto', $resp)
        ->with('config', $config)
        ->with('tributacao', $tributacao)
        ->with('natureza', $natureza)
        ->with('listaCSTCSOSN', $listaCSTCSOSN)
        ->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
        ->with('listaCST_IPI', $listaCST_IPI)
        ->with('anps', $anps)
        ->with('unidadesDeMedida', $unidadesDeMedida)
        ->with('categorias', $categorias)
        ->with('produtoJs', true)
        ->with('title', 'Editar Produto');

    }

    public function pesquisa(Request $request){
        $pesquisa = $request->input('pesquisa');
        $produtos = Produto::where('nome', 'LIKE', "%$pesquisa%")->get();
        $categorias = Categoria::all();

        return view('produtos/list')
        ->with('categorias', $categorias)
        ->with('produtos', $produtos)
        ->with('title', 'Filtro Produto');
    }

    public function filtroCategoria(Request $request){
        $categoria = $request->input('categoria');
        $produtos = Produto::where('categoria_id', $categoria)->get();
        $categorias = Categoria::all();

        $nomeCategoria = Categoria::find($categoria);


        return view('produtos/list')
        ->with('produtos', $produtos)
        ->with('categorias', $categorias)
        ->with('categoria', $nomeCategoria->nome)
        ->with('title', 'Filtro Produto');
    }

    public function receita($id){
        $resp = Produto::
        where('id', $id)
        ->first();  

        return view('produtos/receita')
        ->with('produto', $resp)
        ->with('produtoJs', true)
        ->with('title', 'Receita do Produto');

    }

    public function update(Request $request){
        $product = new Produto();

        $id = $request->input('id');
        $resp = $product
        ->where('id', $id)->first(); 

        $this->_validate($request);
        
        $anps = Produto::lista_ANP();
        $descAnp = '';
        foreach($anps as $key => $a){
            if($key == $request->anp){
                $descAnp = $a;
            }
        }

        $resp->nome = $request->input('nome');
        $resp->categoria_id = $request->input('categoria_id');
        $resp->cor = $request->input('cor');
        $resp->valor_venda = str_replace(",", ".", $request->input('valor_venda'));
        $resp->NCM = $request->input('NCM');
        $resp->CEST = $request->input('CEST') ?? '';

        $resp->CST_CSOSN = $request->input('CST_CSOSN');
        $resp->CST_PIS = $request->input('CST_PIS');
        $resp->CST_COFINS = $request->input('CST_COFINS');
        $resp->CST_IPI = $request->input('CST_IPI');
        // $resp->CFOP = $request->input('CFOP');
        $resp->unidade_venda = $request->input('unidade_venda');
        $resp->unidade_compra = $request->input('unidade_compra');
        $resp->conversao_unitaria = $request->input('conversao_unitaria') ? $request->input('conversao_unitaria') : $resp->conversao_unitaria;
        $resp->codBarras = $request->input('codBarras') ?? 'SEM GTIN';

        $resp->perc_icms = $request->input('perc_icms');
        $resp->perc_pis = $request->input('perc_pis');
        $resp->perc_cofins = $request->input('perc_cofins');
        $resp->perc_ipi = $request->input('perc_ipi');
        $resp->CFOP_saida_estadual = $request->input('CFOP_saida_estadual');
        $resp->CFOP_saida_inter_estadual = $request->input('CFOP_saida_inter_estadual');
        $resp->codigo_anp = $request->input('anp');
        $resp->descricao_anp = $descAnp;

        
        if($request->input('composto')) $resp->composto = 1;
        if($request->input('valor_livre')) $resp->valor_livre = 1;

        $result = $resp->save();
        if($result){
            session()->flash('color', 'green');
            session()->flash('message', 'Produto editado com sucesso!');
        }else{
            session()->flash('color', 'red');
            session()->flash('message', 'Erro ao editar produto!');
        }
        
        return redirect('/produtos'); 
    }

    public function delete($id){
        try{
            $delete = Produto
            ::where('id', $id)
            ->delete();

            if($delete){
                session()->flash('color', 'blue');
                session()->flash('message', 'Registro removido!');
            }else{
                session()->flash('color', 'red');
                session()->flash('message', 'Erro!');
            }
            return redirect('/produtos');
        }catch(\Exception $e){
            return view('errors.sql')
            ->with('title', 'Erro ao deletar produto')
            ->with('motivo', 'Não é possivel remover produtos, presentes vendas, compras ou pedidos!');
        }
    }

    private function _validate(Request $request){
        $rules = [
            'nome' => 'required|max:50',
            'valor_venda' => 'required',
            'NCM' => 'required',
            'perc_icms' => 'required',
            'perc_pis' => 'required',
            'perc_cofins' => 'required',
            'perc_ipi' => 'required',
            'codBarras' => [new EAN13],
            'CFOP_saida_estadual' => 'required',
            'CFOP_saida_inter_estadual' => 'required',
            // 'CEST' => 'required'
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'NCM.required' => 'O campo NCM é obrigatório.',
            // 'CFOP.required' => 'O campo CFOP é obrigatório.',
            'CEST.required' => 'O campo CEST é obrigatório.',
            'valor_venda.required' => 'O campo valor é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.',
            'perc_icms.required' => 'O campo %ICMS é obrigatório.',
            'perc_pis.required' => 'O campo %PIS é obrigatório.',
            'perc_cofins.required' => 'O campo %COFINS é obrigatório.',
            'perc_ipi.required' => 'O campo %IPI é obrigatório.',
            'CFOP_saida_estadual.required' => 'Campo obrigatório.',
            'CFOP_saida_inter_estadual.required' => 'Campo obrigatório.',

        ];
        $this->validate($request, $rules, $messages);
    }

    public function all(){
        $products = Produto::all();
        $arr = array();
        foreach($products as $p){
            $arr[$p->id. ' - ' .$p->nome . ($p->cor != '--' ? ' | Cor: ' . $p->cor : '')] = null;
                //array_push($arr, $temp);
        }
        echo json_encode($arr);
    }

    public function getUnidadesMedida(){
        $unidades = Produto::unidadesMedida();
        
        echo json_encode($unidades);
    }

    public function composto(){
        $products = Produto::
        where('composto', true)
        ->get();
        $arr = array();
        foreach($products as $p){
            $arr[$p->id. ' - ' .$p->nome . ($p->cor != '--' ? ' | Cor: ' . $p->cor : '')] = null;
                //array_push($arr, $temp);
        }
        echo json_encode($arr);
    }

    public function naoComposto(){
        $products = Produto::
        where('composto', false)
        ->get();
        $arr = array();
        foreach($products as $p){
            $arr[$p->id. ' - ' .$p->nome . ($p->cor != '--' ? ' | Cor: ' . $p->cor : '')] = null;
                //array_push($arr, $temp);
        }
        echo json_encode($arr);
    }

    public function getValue(Request $request){
        $id = $request->input('id');
        $product = Product::
        where('id', $id)
        ->first();
        echo json_encode($product->value_sale);
    }

    public function getProduto($id){
        $produto = Produto::
        where('id', $id)
        ->first();
        if($produto->delivery){
            foreach($produto->delivery->pizza as $tp){
                $tp->tamanho;
            }
        }
        echo json_encode($produto);
    }

    public function getProdutoCodBarras($cod){
        $produto = Produto::
        where('codBarras', $cod)
        ->first();

        echo json_encode($produto);
    }

    public function salvarProdutoDaNota(Request $request){
        //echo json_encode($request->produto);
        $produto = $request->produto;

        $valorVenda = str_replace(".", "", $produto['valorVenda']);
        $valorVenda = str_replace(",", ".", $valorVenda);
        $result = Produto::create([
            'nome' => $produto['nome'],
            'NCM' => $produto['ncm'],
            // 'CFOP' => $produto['cfop'],
            'valor_venda' => $valorVenda,
            'valor_livre' => false,
            'cor' => $produto['cor'],
            'conversao_unitaria' => (int) $produto['conversao_unitaria'],
            'categoria_id' => $produto['categoria_id'],
            'unidade_compra' => $produto['unidadeCompra'],
            'unidade_venda' => $produto['unidadeVenda'],
            'codBarras' => $produto['codBarras'] ?? 'SEM GTIN',
            'composto' => false,
            'CST_CSOSN' => $produto['CST_CSOSN'],
            'CST_PIS' => $produto['CST_PIS'],
            'CST_COFINS' => $produto['CST_COFINS'],        
            'CST_IPI' => $produto['CST_IPI'],

        ]);

        echo json_encode($result);  
    }

    public function salvarProdutoDaNotaComEstoque(Request $request){
        //echo json_encode($request->produto);
        $produto = $request->produto;

        $valorVenda = str_replace(",", ".", $produto['valorVenda']);

        $valorCompra = str_replace(",", ".", $produto['valorCompra']);
        $result = Produto::create([
            'nome' => $produto['nome'],
            'NCM' => $produto['ncm'],
            // 'CFOP' => $produto['cfop'],
            'valor_venda' => $valorVenda,
            'valor_livre' => false,
            'cor' => $produto['cor'],
            'conversao_unitaria' => (int) $produto['conversao_unitaria'],
            'categoria_id' => $produto['categoria_id'],
            'unidade_compra' => $produto['unidadeCompra'],
            'unidade_venda' => $produto['unidadeVenda'],
            'codBarras' => $produto['codBarras'] ?? 'SEM GTIN',
            'composto' => false,
            'CST_CSOSN' => $produto['CST_CSOSN'],
            'CST_PIS' => $produto['CST_PIS'],
            'CST_COFINS' => $produto['CST_COFINS'],        
            'CST_IPI' => $produto['CST_IPI'],

        ]);

        $stockMove = new StockMove();
        $stockMove->pluStock($result->id, $produto['quantidade'], $valorCompra);

        echo json_encode($result);  
    }

}
