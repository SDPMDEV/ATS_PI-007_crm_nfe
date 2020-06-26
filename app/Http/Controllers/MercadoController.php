<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DeliveryConfig;
use App\MercadoConfig;
use App\CategoriaProdutoDelivery;
use App\ProdutoDelivery;
use App\ItemPedidoDelivery;
use App\BannerTopo;

class MercadoController extends Controller
{
    public function index(){
        $config = DeliveryConfig::first();
    	$mercadoConfig = MercadoConfig::first();
    	$categoriaBlocos = $this->categoriaBlocos();
    	$itesMaisVendidosDaSemana = ItemPedidoDelivery::maisVendidosDaSemana();
    	$produtos = $this->produtosDeliveryLimit();
        if($itesMaisVendidosDaSemana[0]->id == null) $itesMaisVendidosDaSemana = [];

        $bannersTopo = BannerTopo::all();
        
    	return view('delivery_mercado/index')
    	->with('config', $config)
        ->with('mercadoConfig', $mercadoConfig)
    	->with('categorias', $categoriaBlocos)
    	->with('itesMaisVendidosDaSemana', $itesMaisVendidosDaSemana)
        ->with('produtos', $produtos)
    	->with('bannersTopo', $bannersTopo)
    	->with('title', 'Inicio');
    }

    private function categoriaBlocos(){
    	$categorias = CategoriaProdutoDelivery::all();
    	$sizeBloco1 = 3;
    	$sizeBloco2 = 3;
    	$sizeBloco3 = 3;

    	$arrBloco1 = [];
    	$arrBloco2 = [];
    	$arrBloco3 = [];

    	foreach($categorias as $key => $c){

    		if(sizeof($arrBloco1) < $sizeBloco1){
    			array_push($arrBloco1, $c);
    		} else if(sizeof($arrBloco1) == $sizeBloco1 && sizeof($arrBloco2) < $sizeBloco2){
    			array_push($arrBloco2, $c);
    		} else if(sizeof($arrBloco2) == $sizeBloco2 && sizeof($arrBloco3) < $sizeBloco3){
    			array_push($arrBloco3, $c);
    		}
    	}


    	return [
    		'bloco1' => $arrBloco1,
    		'bloco2' => $arrBloco2,
    		'bloco3' => $arrBloco3,
    	];
    }

    private function produtosDeliveryLimit($limit = 6){
    	$produtos = ProdutoDelivery::
    	limit($limit)
    	->get();
    	return $produtos;
    }

}
