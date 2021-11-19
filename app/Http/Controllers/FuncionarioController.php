<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Funcionario;
use App\ContatoFuncionario;
use App\Usuario;
use App\ComissaoVenda;

class FuncionarioController extends Controller
{
    public function __construct(){
        $this->middleware(function ($request, $next) {
            $value = session('user_logged');
            if(!$value){
                return redirect("/login");
            }else{

                if($value['acesso_financeiro'] == 0){
                    return redirect("/sempermissao");
                }
            }
            return $next($request);
        });
    }

    public function index(){
        $funcionarios = Funcionario::all();

        return view('funcionarios/list')
        ->with('funcionarios', $funcionarios)
        ->with('title', 'Funcionarios');
    }

    public function new(){
        $usuarios = Usuario::all();
        $funcionarios = Funcionario::all();

        $temp = [];
        foreach($usuarios as $u){

            if(!isset($u->funcionario)){
                array_push($temp, $u);
            }
        }

        return view('funcionarios/register')
        ->with('usuarios', $temp)
        ->with('title', 'Cadastrar Funcionario');
    }

    private function parseDate($date){
    	return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
    }

    public function save(Request $request){
        $funcionario = new Funcionario();
        $this->_validate($request);


        $dataRegsitro = $this->parseDate($request->input('data_registro'));
        $request->merge([ 'data_registro' => $dataRegsitro]);
        $request->merge([ 'percentual_comissao' => $request->percentual_comissao ?? 0]);
        $request->merge([ 'usuario_id' => $request->usuario_id != 'NULL' ? $request->usuario_id : null]);


        $result = $funcionario->create($request->all());

        if($result){
            session()->flash("mensagem_sucesso", "Funcionario cadastrado com sucesso!");
        }else{
            session()->flash('mensagem_erro', 'Erro ao cadastrar funcionario!');
        }
        
        return redirect('/funcionarios');
    }

    public function edit($id){
        $usuarios = Usuario::all();
        $funcionario = new Funcionario(); //Model
        
        $resp = $funcionario
        ->where('id', $id)->first(); 

        $temp = [];
        foreach($usuarios as $u){
            if(!isset($u->funcionario)){
                array_push($temp, $u);
            }
        } 

        return view('funcionarios/register')
        ->with('pessoaFisicaOuJuridica', true)
        ->with('funcionario', $resp)
        ->with('usuarios', $temp)
        ->with('title', 'Editar Funcionario');

    }

    public function update(Request $request){
        $funcionario = new Funcionario();

        $id = $request->input('id');
        $resp = $funcionario
        ->where('id', $id)->first(); 

        $request->merge([ 'data_registro' => $this->parseDate($request->input('data_registro'))]);

        $this->_validate($request);
        

        $resp->nome = $request->input('nome');
        $resp->cpf = $request->input('cpf');

        $resp->rua = $request->input('rua');
        $resp->numero = $request->input('numero');
        $resp->bairro = $request->input('bairro');

        $resp->telefone = $request->input('telefone');
        $resp->celular = $request->input('celular');
        $resp->email = $request->input('email');
        $resp->percentual_comissao = $request->input('percentual_comissao') ?? 0;

        $resp->usuario_id = ($request->usuario_id && $request->usuario_id != 'NULL') ? $request->usuario_id : null;

        $result = $resp->save();
        if($result){
            session()->flash('mensagem_sucesso', 'Funcionario editado com sucesso!');
        }else{
            session()->flash('mensagem_erro', 'Erro ao editar funcionario!');
        }
        
        return redirect('/funcionarios'); 
    }

    public function delete($id){
        $delete = Funcionario
        ::where('id', $id)
        ->delete();
        if($delete){
            session()->flash('mensagem_sucesso', 'Registro removido!');
        }else{
            session()->flash('mensagem_erro', 'Erro!');
        }
        return redirect('/funcionarios');
    }


    private function _validate(Request $request){
        $rules = [
            'nome' => 'required|max:50',
            'cpf' => 'required',
            'rua' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'telefone' => 'required|max:20',
            'celular' => 'required|max:20',
            'email' => 'required|email|max:40',
            'rg' => 'required',
            'data_registro' => 'required',
        ];

        $messages = [
            'nome.required' => 'O campo Nome é obrigatório.',
            'data_registro.required' => 'O campo data de registro é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.',
            'cpf.required' => 'O campo CPF é obrigatório.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'rg.required' => 'O campo IE/RG é obrigatório.',
            'rua.max' => '80 caracteres maximos permitidos.',
            'numero.required' => 'O campo Numero é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'telefone.required' => 'O campo telefone é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',
            'celular.required' => 'O campo celular  é obrigatório.',
            'celular.max' => '20 caracteres maximos permitidos.',

            'email.required' => 'O campo Email é obrigatório.',
            'email.max' => '40 caracteres maximos permitidos.',
            'email.email' => 'Email inválido.',


        ];
        $this->validate($request, $rules, $messages);
    }

    private function _validateContato(Request $request){
        $rules = [
            'nome' => 'required|max:40',
            'telefone' => 'required|max:20',
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '40 caracteres maximos permitidos.',
            
            'telefone.required' => 'O campo Celular é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',

        ];
        $this->validate($request, $rules, $messages);
    }

    public function contatos($id, $edit = false){
        $funcionario = Funcionario::
        where('id', $id)
        ->first();

        return view('funcionarios/contatos')
        ->with('funcionario', $funcionario)
        ->with('edit', $edit)
        ->with('title', 'Contato Funcionario');
    }

    public function editContato($id){
        $contato = ContatoFuncionario::
        where('id', $id)
        ->first();

        $funcionario = $contato->funcionario;

        return view('funcionarios/contatos')
        ->with('funcionario', $funcionario)
        ->with('contato', $contato)
        ->with('title', 'Contato Funcionario');
    }

    public function deleteContato($id){
        $funcionario = ContatoFuncionario::
        where('id', $id)
        ->first();

        $delete = $funcionario->delete();

        if($delete){
            session()->flash('mensagem_sucesso', 'Registro removido!');
        }else{
            session()->flash('mensagem_erro', 'Erro!');
        }

        return redirect("/funcionarios/contatos/$funcionario->id");
    }

    public function saveContato(Request $request){
        $this->_validateContato($request);

        $result = null;
        if($request->id > 0){
            $contato = ContatoFuncionario::
            where('id', $request->id)
            ->first();

            $contato->nome = $request->nome;
            $contato->telefone = $request->telefone;

            $result = $contato->save();
        }else{
            $result = ContatoFuncionario::create($request->all());
        }
        if($result){
            session()->flash("mensagem_sucesso", "Contato cadastrado/editado com sucesso!");
        }else{
            session()->flash('mensagem_erro', 'Erro ao cadastrar contato!');
        }
        
        return redirect("/funcionarios/contatos/$request->funcionario_id");
    }

    public function all(){
        $funcionarios = Funcionario::all();
        $arr = array();
        foreach($funcionarios as $c){
            $arr[$c->id. ' - ' .$c->nome] = null;
                //array_push($arr, $temp);
        }
        echo json_encode($arr);
    }

    public function comissao(){
        $funcionarios = Funcionario::all();
        $comissoes = ComissaoVenda::limit(200)
        ->orderBy('id', 'desc')
        ->get();

        return view('funcionarios/comissao')
        ->with('funcionarios', $funcionarios)
        ->with('comissoes', $comissoes)
        ->with('comissaoJs', true)
        ->with('title', 'Lista de comissões');
    }

    public function pagarComissao(Request $request){
        try{
            $vArr = $arr = $request->arr;
            $arr = explode(",", $arr);

            foreach($arr as $a){

                $pedido = ComissaoVenda::find($a);
                $pedido->status = 1;

                $pedido->save();
            }
            session()->flash('mensagem_sucesso', 'Comissão(s) paga(s) com sucesso!');
        }catch(\Exception $e){
            session()->flash('mensagem_erro', 'Erro ao pagar comissão(s)!');

        }
        return redirect()->back();
    }

    public function comissaoFiltro(Request $request){
        $funcionarioId = $request->funcionario_id;
        $status = $request->status;
        $dataInicial = $request->data_inicial;
        $dataFinal = $request->data_final;

        $comissoes = ComissaoVenda::
        orderBy('id', 'desc');

        if($status != '--'){
            $comissoes->where('status', $status);
        }

        if($dataFinal && $dataInicial){
            $data_inicial = $this->parseDate($request->data_inicial);
            $data_final = $this->parseDate($request->data_final, true);
            $comissoes->whereBetween('created_at', [$data_inicial, 
                $data_final]);
        }

        if($funcionarioId != '--'){
            $comissoes->where('funcionario_id', $funcionarioId);
        }
        $comissoes = $comissoes->get();

        $funcionarios = Funcionario::all();

        return view('funcionarios/comissao')
        ->with('funcionarios', $funcionarios)
        ->with('comissoes', $comissoes)
        ->with('comissaoJs', true)
        ->with('title', 'Lista de comissões');
    }
}
