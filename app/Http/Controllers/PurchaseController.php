<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Compra;
use App\ItemPurchase;
use App\Helpers\StockMove;
use App\Services\NFeEntradaService;
use App\ConfigNota;
use App\NaturezaOperacao;

class PurchaseController extends Controller
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
        $totalRegistros = count(Compra::all());
        $compras = Compra::
        orderBy('id', 'desc')
        ->paginate(15);

        $somaCompraMensal = $this->somaCompraMensal();
        return view('compraManual/listAll')
        ->with('compras', $compras)
        ->with('somaCompraMensal', $somaCompraMensal)
        ->with('links', true)
        ->with('graficoJs', true)
        ->with('title', 'Compras');
        
    }

    public function pesquisa(Request $request){
        $compras = Compra::pesquisaProduto($request->pesquisa);
        $totalRegistros = count($compras);

        $somaCompraMensal = $this->somaCompraMensal();
        return view('compraManual/listAll')
        ->with('compras', $compras)
        ->with('somaCompraMensal', $somaCompraMensal)
        ->with('graficoJs', true)
        ->with('title', 'Pequisa de Produto em Compras');
        
    }

    
    private function somaCompraMensal(){
        $compras = Compra::all();
        $temp = [];
        $soma = 0;
        $mesAnterior = null;
        $anoAnterior = null;

        foreach($compras as $c){
            $date = $c->date_register;
            $mes = substr($date, 5, 2);
            $ano = substr($date, 0, 4);

            if($mesAnterior != $mes){
                $temp["Mes: ".$mes."/$ano"] = $c->valor;
            }else{
                $temp["Mes: ".$mesAnterior."/$anoAnterior"] += $c->valor;
            }
            $mesAnterior = $mes;
            $anoAnterior = $ano;
        }

        return $temp;
    }

    

    private function somaCompraMensalFiltro($compras){
        $temp = [];
        $soma = 0;
        $mesAnterior = null;
        $anoAnterior = null;

        foreach($compras as $c){
            $date = $c->date_register;
            $mes = substr($date, 5, 2);
            $ano = substr($date, 0, 4);

            if($mesAnterior != $mes){
                $temp["Mes: ".$mes."/$ano"] = $c->valor;
            }else{
                $temp["Mes: ".$mesAnterior."/$anoAnterior"] += $c->valor;
            }
            $mesAnterior = $mes;
            $anoAnterior = $ano;
        }

        return $temp;
    }

    private function somaCompraDiarioFiltro($compras){
        $temp = [];
        $soma = 0;
        $diaAnterior = null;
        $mesAnterior = null;
        $s = 0;

        foreach($compras as $c){
            $date = $c->date_register;
            $dia = substr($date, 8, 2);
            $mes = substr($date, 5, 2);
            if($diaAnterior != $dia){
                $temp["Dia: ".$dia."/$mes"] = $c->valor;
            }else{
                $temp["Dia: ".$diaAnterior."/$mesAnterior"] += $c->valor;
                $s += $c->valor;
            }
            $mesAnterior = $mes;
            $diaAnterior = $dia;
        }

        return $temp;
    }
    private function diferencaEntreDatas($data1, $data2){
        $dif = strtotime($data2) - strtotime($data1);
        return floor($dif / (60 * 60 * 24));
    }

    public function filtro(Request $request){
        $dataInicial = $request->data_inicial;
        $dataFinal = $request->data_final;
        $fornecedor = $request->fornecedor;
        $compras = null;
        $diferencaDatas = null;
        if(isset($fornecedor) && isset($dataInicial) && isset($dataFinal)){
            $compras = Compra::filtroDataFornecedor($fornecedor, $this->parseDate($dataInicial), $this->parseDate($dataFinal, true));
            $diferencaDatas = $this->diferencaEntreDatas($this->parseDate($dataInicial), $this->parseDate($dataFinal));
        }else if(isset($dataInicial) && isset($dataFinal)){
            $compras = Compra::filtroData($this->parseDate($dataInicial), $this->parseDate($dataFinal, true)
        );
            $diferencaDatas = $this->diferencaEntreDatas($this->parseDate($dataInicial), $this->parseDate($dataFinal));

        }else if(isset($fornecedor)){
            $compras = Compra::filtroFornecedor($fornecedor);
        }


        if($diferencaDatas > 31 || $diferencaDatas == null){$somaCompraMensal = $this->somaCompraMensalFiltro($compras);
        }else{
            $somaCompraMensal = $this->somaCompraDiarioFiltro($compras);
        }

        return view('compraManual/listAll')
        ->with('compras', $compras)
        ->with('fornecedor', $fornecedor)
        ->with('dataInicial', $dataInicial)
        ->with('dataFinal', $dataFinal)
        ->with('somaCompraMensal', $somaCompraMensal)
        ->with('graficoJs', true)
        ->with('infoDados', "Contas filtradas")
        ->with('title', 'Filtro Compras');

    }

    private function parseDate($date, $plusDay = false){

        if($plusDay == false)
            return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
        else
            return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
    }

    public function downloadXml($id){
        $compra = Compra::
        where('id', $id)
        ->first();
        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
        return response()->download($public.'xml_entrada/'.$compra->xml_path);
    }


    public function detalhes($id){
        $compra = Compra::
        where('id', $id)
        ->first();

        return view('compraManual/detail')
        ->with('compra', $compra)
        ->with('title', 'Detalhes da compra');
    }

    public function delete($id){
        $compra = Compra::
        where('id', $id)
        ->first();

        $stockMove = new StockMove();
        $public = getenv('SERVIDOR_WEB') ? 'public/' : '';
        echo $public."xml_entrada/$compra->xml_path";
        if(file_exists($public."xml_entrada/$compra->xml_path")){
            unlink($public."xml_entrada/$compra->xml_path");
        }
        foreach($compra->itens as $i){
        // baixa de estoque
            $stockMove->downStock($i->produto->id, $i->quantidade);
            $i->delete();
        } 

        if($compra->delete()){
            session()->flash('color', 'blue');
            session()->flash('message', 'Registro removido!');
        }else{
            session()->flash('color', 'red');
            session()->flash('message', 'Erro!');
        }
        return redirect('/compras');
    }

    public function emitirEntrada($id){
        $compra = Compra::find($id);
        $naturezas = NaturezaOperacao::all();
        return view('compraManual/emitirEntrada')
        ->with('compra', $compra)
        ->with('naturezas', $naturezas)
        ->with('NFeEntradaJS', true)
        ->with('title', 'Emitir NF-e Entrada');
    }

    public function gerarEntrada(Request $request){
        $compra = Compra::find($id);
        $config = ConfigNota::first();

        $cnpj = str_replace(".", "", $config->cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $nfe_service = new NFeEntradaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => getenv('CSC'),
            "CSCid" => getenv('CSCid')
        ], 55);

        header('Content-type: text/html; charset=UTF-8');

        $nfe = $nfe_service->gerarNFe($vendaId);


    }

}
