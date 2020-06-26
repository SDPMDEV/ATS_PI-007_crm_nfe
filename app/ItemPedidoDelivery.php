<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPedidoDelivery extends Model
{
    protected $fillable = [
      'pedido_id', 'produto_id', 'status', 'quantidade', 'observacao', 'tamanho_id'
  ];

  public function produto(){
    return $this->belongsTo(ProdutoDelivery::class, 'produto_id');
}

public function tamanho(){
    return $this->belongsTo(TamanhoPizza::class, 'tamanho_id');
}

public function itensAdicionais(){
    return $this->hasMany('App\ItemPedidoComplementoDelivery', 'item_pedido_id', 'id');
}

public function pedido(){
    return $this->belongsTo(PedidoDelivery::class, 'pedido_id');
}

public function sabores(){
    return $this->hasMany('App\ItemPizzaPedido', 'item_pedido', 'id');
}

public function valorProduto(){
    if(sizeof($this->sabores) == 0){
        $valor = $this->produto->valor;
        foreach($this->itensAdicionais as $ad){
            $valor += $ad->adicional->valor;
        }
        return $valor;
    }else{

        $maiorValor = 0;
        $somaValores = 0;

        foreach($this->sabores as $sb){

            $sb->produto->produto;

            $v = $sb->maiorValor($sb->sabor_id, $this->tamanho_id);
            $somaValores += $v;

            if($v > $maiorValor) $maiorValor = $v;

        }

        if(getenv("DIVISAO_VALOR_PIZZA") == 1){

            $maiorValor = $somaValores/sizeof($this->sabores);
        }

        foreach($this->itensAdicionais as $ad){
            $maiorValor += $ad->adicional->valor;
        }
        return $maiorValor;
    }
}

public function nomeDoProduto(){

    if(sizeof($this->sabores) == 0){
        $nome = $this->produto->produto->nome;
        if($this->observacao != '') $nome .= " | OBS: " . $this->observacao;
        return $nome;
    }else{
        $cont = 1;
        $nome = "";
        foreach($this->sabores as $s){
            $nome .= $cont."/".count($this->sabores) . " " . $s->produto->produto->nome;
        }
        $nome .= " | Tamanho: " . $this->tamanho->nome;

        if($this->observacao != '') $nome .= " | OBS: " . $this->observacao;
        return $nome;
    }
}

public static function maisVendidosDaSemana(){

    $dataInicial = date('Y-m-d', strtotime("-7 days"));
    $dataFinal = date('Y-m-d');

    $c = ItemPedidoDelivery::
    selectRaw('item_pedido_deliveries.*, sum(item_pedido_deliveries.quantidade) as soma')
    ->whereBetween('created_at', [$dataInicial, 
        $dataFinal])
    ->orderBy('soma', 'asc')
    ->limit(4);
    return $c->get();
}
}
