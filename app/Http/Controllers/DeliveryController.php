<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CategoriaProdutoDelivery;
use App\ProdutoDelivery;
use App\ClienteDelivery;
use Mail;
use App\DeliveryConfig;
use App\ProdutoPizza;
use App\TamanhoPizza;
use App\TokenWeb;
use Comtele\Services\TextMessageService;
use App\Rules\CelularDup;

class DeliveryController extends Controller
{
    protected $config = null;

    public function __construct(){
        $this->config = DeliveryConfig::first();
        $delivery = getenv("DELIVERY");

    }

    public function index(){
        $clienteLog = session('cliente_log');
        session()->forget('tamanho_pizza');
        session()->forget('sabores');
        $categorias = CategoriaProdutoDelivery::all();
        $destaques = ProdutoDelivery::
        where('destaque', true)
        ->where('status', true)
        ->get();

        if($this->config == null || getenv("DELIVERY") == 0) {
            return redirect('/login');
        }

        return view('delivery/index')
        ->with('categorias', $categorias)
        ->with('destaques', $destaques)
        ->with('config', $this->config)
        ->with('tokenJs', true)
        ->with('cliente_logado', $clienteLog['nome'])
        ->with('title', 'INICIO');
    }

    public function cardapio(){
        session()->forget('tamanho_pizza');
        session()->forget('sabores');
        $categorias = CategoriaProdutoDelivery::all();

        return view('delivery/categorias')
        ->with('categorias', $categorias)
        ->with('config', $this->config)
        ->with('tokenJs', true)
        ->with('title', 'CARDÁPIO');
    }

    public function produtos($id){

        $categoria = CategoriaProdutoDelivery::where('id', $id)->first();

        if(strpos(strtolower($categoria->nome), 'izza') !== false){

            $tamanhos = TamanhoPizza::all();
            return view('delivery/tipoPizza')
            ->with('tamanhos', $tamanhos)
            ->with('config', $this->config)
            ->with('title', 'TIPO DA PIZZA'); 

        }else{
         return view('delivery/produtos')
         ->with('produtos', $categoria->produtos)
         ->with('categoria', $categoria)
         ->with('config', $this->config)
         ->with('title', 'PRODUTOS'); 
     }

 }

 public function verProduto($id){

    $produto = ProdutoDelivery
    ::where('id', $id)
    ->first();

    return view('delivery/ver_produto')
    ->with('produto', $produto)
    ->with('config', $this->config)
    ->with('title', 'ADICIONAR'); 


}

public function escolherSabores(Request $request){
    $tipo = $request->tipo;
    $tamanho = explode("-", $tipo)[0];
    $sabores = explode("-", $tipo)[1];
    
    $session = [
        'tamanho' => $tamanho,
        'sabores' => $sabores
    ];
    session(['tamanho_pizza' => $session]);


    $t = TamanhoPizza::
    where('nome', $tamanho)
    ->first();

    $sabores = session('sabores');

    $saboresIncluidos = [];
    if($sabores){
        foreach($sabores as $s){
            $p = ProdutoDelivery::
            where('id', $s)
            ->first();

            $p->produto;
            $p->galeria;

            array_push($saboresIncluidos, $p);
        }
    }

    return view('delivery/pizzas')
    ->with('pizzas', $t->produtoPizza)
    ->with('config', $this->config)
    ->with('pizzaJs', true)
    ->with('saboresIncluidos', $saboresIncluidos)
    ->with('title', 'PIZZAS'); 
}

public function pesquisa(Request $request){
    $pesquisa = $request->input('pesquisa');
    // $pizzas = ProdutoDelivery::
    // select('produto_pizzas.*')
    // ->join('produto_deliveries', 'produto_pizzas.produto_id', '=', 'produto_deliveries.id')
    // ->join('produtos', 'produtos.id', '=', 'produto_deliveries.produto_id')
    // ->where('produtos.nome', 'LIKE', "%$pesquisa%")->get();
    $tamanho = session('tamanho_pizza');
    $produtos = ProdutoPizza::
    select('produto_pizzas.*')
    ->join('produto_deliveries', 'produto_pizzas.produto_id', '=', 'produto_deliveries.id')
    ->join('tamanho_pizzas', 'produto_pizzas.tamanho_id', '=', 'tamanho_pizzas.id')
    ->join('produtos', 'produtos.id', '=', 'produto_deliveries.produto_id')
    ->where('produtos.nome', 'LIKE', "%$pesquisa%")
    ->where('tamanho_pizzas.nome', $tamanho['tamanho'])
    ->get();

    $sabores = session('sabores');
    $saboresIncluidos = [];
    if($sabores){
        foreach($sabores as $s){
            $p = ProdutoDelivery::
            where('id', $s)
            ->first();

            $p->produto;
            $p->galeria;

            array_push($saboresIncluidos, $p);
        }
    }

    return view('delivery/pizzas')
    ->with('pizzas', $produtos)
    ->with('config', $this->config)
    ->with('pizzaJs', true)
    ->with('pesquisa', true)
    ->with('saboresIncluidos', $saboresIncluidos)
    ->with('title', 'PIZZAS'); 
}

public function adicionais(){
    //Pizza
    $value = session('cliente_log');
    if($value){
        $sabores = session('sabores');
        $tamanho = session('tamanho_pizza');
        $saboresIncluidos = [];
        $tamanhoId = 0;

        $maiorValor = 0;
        if($sabores){
            foreach($sabores as $s){
                $p = ProdutoDelivery::
                select('produto_deliveries.*')
                ->join('produto_pizzas', 'produto_pizzas.produto_id', '=', 'produto_deliveries.id')
                ->join('tamanho_pizzas', 'produto_pizzas.tamanho_id', '=', 'tamanho_pizzas.id')
                ->where('produto_deliveries.id', $s)
                ->where('tamanho_pizzas.nome', $tamanho['tamanho'])
                ->first();

                $p->produto;
                $p->galeria;

                array_push($saboresIncluidos, $p);

                foreach($p->pizza as $t){
                    if($t->tamanho->nome == $tamanho['tamanho']){
                        $tamanhoId = $t->tamanho->id;
                        if($t->valor > $maiorValor){
                            $maiorValor = $t->valor;
                        }
                    }
                }
            }
        }

        $produto = $saboresIncluidos[0];

    // print_r($produto->pizza);

        return view('delivery/adicionalPizza')
        ->with('maiorValor', $maiorValor)
        ->with('saboresIncluidos', $saboresIncluidos)
        ->with('acompanhamentoPizza', true)
        ->with('sabores', $sabores)
        ->with('tamanho', $tamanhoId)
        ->with('adicionais', $produto->categoria->adicionais)
        ->with('config', $this->config)
        ->with('title', 'Adicionais para Pizza');
    }else{
        session()->flash("message_erro", "Voçe precisa estar logado para comprar nossos produtos");
        return redirect('/autenticar'); 
    }
}

public function pizzas(Request $request){
    $categoria = CategoriaProdutoDelivery::
    where('nome', 'like', '%izza%')
    ->first();

    foreach($categoria->produtos as $p){
        if($p->produto->delivery){
            $p->produto->delivery->galeria;
            foreach($p->produto->delivery->pizza as $pp){
                if($request->tamanho == $pp->tamanho_id){
                   $p->tamanhoValor = $pp->valor;
               }
           }

       } else{
         $p->produto;
         $p->tamanhoValor = 0;
     }
 }

 echo json_encode($categoria->produtos);
}


public function adicionarSabor(Request $request){
    $sabores = session('sabores');
    if($sabores){
        array_push($sabores, $request->pizza_id);

        session(['sabores' => $sabores]);
    }else{
        $session = [
            $request->pizza_id,
        ];
        session(['sabores' => $session]);
    }
    return redirect()->back();
}

public function removeSabor($id){
    $sabores = session('sabores');
    $temp = [];
    if($sabores){
        foreach($sabores as $s){
            if($s != $id){
                array_push($temp, $s);
            }
        }
        session(['sabores' => $temp]);
    }
    return redirect()->back();
}

public function verificaPizzaAdicionada(Request $request){
    $sabores = session('sabores');
    if($sabores){
        foreach($sabores as $s){
            if(in_array($request->pizza_id, $sabores)){
                return json_encode(true);
            }
        }
        return json_encode(false);
    }else{
        return json_encode(false);
    }
}

public function acompanhamento($id){
    $value = session('cliente_log');
    if($value){
        $produto = ProdutoDelivery::where('id', $id)
        ->first();


        if(strpos(strtolower($produto->categoria->nome), 'izza') !== false){
            $tamanhos = TamanhoPizza::all();
            return view('delivery/tipoPizza')
            ->with('tamanhos', $tamanhos)
            ->with('config', $this->config)
            ->with('title', 'TIPO DA PIZZA'); 
        }else{

            return view('delivery/acompanhamentos')
            ->with('produto', $produto)
            ->with('acompanhamento', true)
            ->with('adicionais', $produto->categoria->adicionais)
            ->with('config', $this->config)
            ->with('title', 'ACOMPANHAMENTO');
        }
    }else{
        session()->flash("message_erro", "Voçe precisa estar logado para comprar nossos produtos");
        return redirect('/autenticar'); 
    }
}

public function login(){
    return view('delivery/login')
    ->with('config', $this->config)
    ->with('tokenJs', true)
    ->with('title', 'AUTENTICAR');
}

private function setaMascaraPhone($phone){
    $n = substr($phone, 0, 2) . " ";
    $n .= substr($phone, 2, 5)."-";
    $n .= substr($phone, 7, 4);
    return $n;
}

public function autenticar(Request $request){
    $mailPhone = $request->mail_phone;
    $mailPhone = str_replace(" ", "", $mailPhone);
    $senha = md5($request->senha);
    $cliente = null;
    if(is_numeric($mailPhone)){

        if(strlen($mailPhone) != 11){
            session()->flash('message_erro_telefone', 'Digite o telefone seguindo este padrao de exemplo 43999998888 - 11 Digitos.');
            return redirect("/autenticar");
        }

        $cliente = ClienteDelivery::where('celular', $this->setaMascaraPhone($mailPhone))
        ->where('senha', $senha)
        ->first();

    }else{
        $cliente = ClienteDelivery::where('email', $mailPhone)
        ->where('senha', $senha)
        ->first();
    }

    if($cliente == null){
        session()->flash('message_erro', 'Credenciais inválidas.');
        return redirect('/autenticar');
    }else{

        if($cliente->ativo == 0){
            return view('delivery/ativar')
            ->with('config', $this->config)
            ->with('cliente', $cliente)
            ->with('login_ative', true)
            ->with('title', 'ATIVAR CADASTRO');
        }
        $session = [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
        ];
        session(['cliente_log' => $session]);
        session()->flash("message_sucesso", "Bem vindo ". $cliente->nome);
        return redirect('/'); 
    }

}

public function refreshToken(Request $request){
    $cliente = ClienteDelivery::where('id', $request->id)
    ->first();
    $cod = rand(100000, 888888);

    $celular = $cliente->celular;
    $celular = str_replace(" ", "", $celular);
    $celular = str_replace("-", "", $celular);
    if(getenv("AUTENTICACAO_SMS") == 1) $this->sendSms($celular, $cod);
    // $this->sendEmailCod($cliente->email, $cod);
    if(getenv("AUTENTICACAO_EMAIL") == 1 && getenv("SERVIDOR_WEB") == 1) $this->sendEmailLink($cliente->email, $cod);
    $cliente->token = $cod;
    if($cliente->save())
        return response()->json($cliente, 200);
    else 
        return response()->json(false, 204);

}



public function logoff(){
    session()->forget('cliente_log');

    session()->flash('message_erro', 'Logoff realizado.');
    return redirect("/autenticar");
}


public function registro(){
    $clienteLog = session('cliente_log');
    if(!$clienteLog){
        return view('delivery/registro')
        ->with('config', $this->config)
        ->with('title', 'REGITRAR-SE');
    }else{
        session()->flash("message_sucesso", "Voçe já esta logado ".$clienteLog['nome']);
        return redirect('/'); 
    }
}

public function salvarRegistro(Request $request){

    $this->_validate($request);
    $cod = rand(100000, 888888);
    $request->merge([ 'senha' => md5($request->senha)]);
    $request->merge([ 'ativo' => false]);
    $request->merge([ 'token' => $cod]);

    $result = ClienteDelivery::create($request->all());
    if($result){

        $celular = $request->celular;
        $celular = str_replace(" ", "", $celular);
        $celular = str_replace("-", "", $celular);
        if(getenv("AUTENTICACAO_SMS") == 1) $this->sendSms($celular, $cod);
        // $this->sendEmailCod($request->email, $cod);
        if(getenv("AUTENTICACAO_EMAIL") == 1 && getenv("SERVIDOR_WEB") == 1) 
            $this->sendEmailLink($request->email, $cod);

        return view('delivery/autenticarCliente')
        ->with('config', $this->config)
        ->with('celular', $celular)
        ->with('cadastro_ative', true)
        ->with('title', 'AUTENTICAR');

    }else{
        session()->flash('message_erro', 'Erro ao se registrar!');
        return redirect('/');
    }
}

private function sendSms($phone, $cod){
    $nomeEmpresa = getenv('SMS_NOME_EMPRESA');
    $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
    $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
    $content = $nomeEmpresa. " codigo de Autorizacao ". $cod;
    $textMessageService = new TextMessageService(getenv('SMS_KEY'));
    $res = $textMessageService->send("Sender", $content, [$phone]);
    return $res;
}

private function sendEmailCod($email, $cod){
    Mail::send('mail.codigo_verifica', ['cod' => $cod], function($m) use ($email){
        $nomeEmail = getenv('MAIL_NAME');
        $nomeEmail = str_replace("_", " ", $nomeEmail);
        $m->from(getenv('MAIL_USERNAME'), $nomeEmail);
        $m->subject('Autenticação');
        $m->to($email);
    });
}


public function validaToken(Request $request){
    $token = $request->codToken;
    $celular = $request->celular;

    if(substr($celular, 8,1) != "-")
        $validCelular = $this->setMask($celular);
    else
        $validCelular = $celular;

    $cliente = ClienteDelivery::where('celular', $validCelular)
    ->first();

    if($cliente->token == $token){
        $cliente->ativo = true;
        $cliente->save();
        $session = [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
        ];
        session(['cliente_log' => $session]);
        session()->flash("message_sucesso", "Bem vindo ". $cliente->nome."!");
        return response()->json(true, 200);
    }else{
        session()->flash("message_erro", "Código de verificação inválido");
        return response()->json(false, 204);
    }
}

private function setMask($celular){
    $c = substr($celular, 0, 2) . " " . 
    substr($celular, 2,5) . "-" . substr($celular, 7,4);
    return $c;
}

private function _validate(Request $request){
    $rules = [
        'nome' => 'required|max:30',
        'sobre_nome' => 'required|max:30',
        'senha' => 'required|min:4|max:10|same:senha_confirma',
        'celular' => ['required','min:13', 'max:15',new CelularDup],
        'email' => 'required|max:50|email',
        'senha_confirma' => 'required'

    ];

    $messages = [
        'nome.required' => 'O campo nome é obrigatório.',
        'nome.max' => 'Maximo de 30 caracteres',
        'sobre_nome.required' => 'O campo sobre nome é obrigatório.',
        'sobre_nome.max' => 'Maximo de 30 caracteres',
        'senha.required' => 'O campo senha é obrigatório.',
        'senha.max' => 'Maximo de 10 caracteres',
        'senha.min' => 'Maximo de 4 caracteres',
        'senha.same' => 'Senhas não coincidem',
        'senha_confirma.required' => 'O campo confirma senha é obrigatório',
        'celular.required' => 'O campo celular é obrigatório.',
        'celular.min' => 'Minimo de 15 caracteres',
        'celular.max' => 'Maximo de 15 caracteres',
        'email.required' => 'O campo email é obrigatório.',
        'email.max' => 'Maximo de 50 caracteres',
        'email.email' => 'Email inválido'
    ];
    $this->validate($request, $rules, $messages);
}

public function recuperarSenha(){
    return view('delivery/recuperarSenha')
    ->with('config', $this->config)
    ->with('title', 'Recuperar Senha');
}

public function enviarSenhaEmail(Request $request){
    $mailPhone = $request->mail_phone;
    $mailPhone = str_replace(" ", "", $mailPhone);
    $cliente = null;
    if(is_numeric($mailPhone)){
        if(strlen($mailPhone) != 11){
            session()->flash('message_erro_telefone', 'Digite o telefone seguindo este padrao de exemplo 43999998888 - 11 Digitos.');
            return redirect("/autenticar");
        }

        $cliente = ClienteDelivery::where('celular', $this->setaMascaraPhone($mailPhone))
        ->first();

    }else{
        $cliente = ClienteDelivery::where('email', $mailPhone)
        ->first();
    }

    if($cliente == null){
        session()->flash('message_erro', 'Email ou telefone não cadastrado.');
        return redirect('/autenticar/esqueceu_a_senha');
    }else{
        $newPass = $this->randomPassword();
        Mail::send('mail.nova_senha', ['senha' => $newPass], function($m) use ($cliente){

            $nomeEmail = getenv('MAIL_NAME');
            $nomeEmail = str_replace("_", " ", $nomeEmail);
            $m->from(getenv('MAIL_USERNAME'), $nomeEmail);

            $m->subject('recuperacao de senha');
            $m->to($cliente->email);
        });
        $cliente->senha = md5($newPass);
        $cliente->save();
        session()->flash('message_sucesso', 'Email enviado com sua nova senha.');
        return redirect('/autenticar/esqueceu_a_senha');
    }

}

private function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
    $pass = array(); 
    $alphaLength = strlen($alphabet) - 1; 
    for ($i = 0; $i < 4; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); 
}

public function saveTokenWeb(Request $request){
    $tk = TokenWeb::
    where('token', $request->token)
    ->first();

    if($tk == null){
        $res = TokenWeb::create([
            'token' => $request->token,
            'cliente_id' => $request->cliente_logado > 0 ? $request->cliente_logado : null
        ]);
        echo json_encode('insert');

    }else{
        if($request->cliente_logado > 0){
            $tk->cliente_id = $request->cliente_logado;
            $tk->save();
        }
        echo json_encode('update');
    }
}

private function sendEmailLink($email, $cod){
    Mail::send('mail.link_verifica', ['link' => md5("$cod-$email")], function($m) use ($email){
        $nomeEmail = getenv('MAIL_NAME');
        $nomeEmail = str_replace("_", " ", $nomeEmail);
        $m->from(getenv('MAIL_USERNAME'), $nomeEmail);
        $m->subject('Autenticação');
        $m->to($email);
    });
}

public function autenticarClienteEmail($cod){

    $clientes = ClienteDelivery::all();
    $cliente = null;
    foreach($clientes as $c){
        if(md5("$c->token-$c->email") == $cod){
            $c->ativo = true;
            $c->save();
            $cliente = $c;
        }
    }

    if($cliente != null){
        $session = [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
        ];
        session(['cliente_log' => $session]);
        session()->flash("message_sucesso", "Bem vindo ". $cliente->nome . ", habilitado para App e Webdelivery");
        return redirect('/'); 
    }else{
        echo "Erro";
    }

}


}
