<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Categoria;

class CategoryController extends Controller
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
        $categorias = Categoria::all();
        return view('categorias/list')
        ->with('categorias', $categorias)
        ->with('title', 'Categorias');
    }

    public function new(){
        return view('categorias/register')
        ->with('title', 'Cadastrar Categoria');
    }

    public function save(Request $request){
        $category = new Categoria();
        $this->_validate($request);

        $result = $category->create($request->all());

        if($result){
            session()->flash('color', 'blue');
            session()->flash("message", "Categoria cadastrada com sucesso.");
        }else{
            session()->flash('color', 'red');
            session()->flash('message', 'Erro ao cadastrar categoria.');
        }

        return redirect('/categorias');
    }

    public function edit($id){
        $categoria = new Categoria(); 

        $resp = $categoria
        ->where('id', $id)->first();  

        return view('categorias/register')
        ->with('categoria', $resp)
        ->with('title', 'Editar Categoria');

    }

    public function update(Request $request){
        $categoria = new Categoria();

        $id = $request->input('id');
        $resp = $categoria
        ->where('id', $id)->first(); 

        $this->_validate($request);


        $resp->nome = $request->input('nome');

        $result = $resp->save();
        if($result){
            session()->flash('color', 'green');
            session()->flash('message', 'Categoria editada com sucesso!');
        }else{
            session()->flash('color', 'red');
            session()->flash('message', 'Erro ao editar categoria!');
        }

        return redirect('/categorias'); 
    }

    public function delete($id){
        try{
            $delete = Categoria
            ::where('id', $id)
            ->delete();
            if($delete){
                session()->flash('color', 'blue');
                session()->flash('message', 'Registro removido!');
            }else{
                session()->flash('color', 'red');
                session()->flash('message', 'Erro!');
            }
            return redirect('/categorias');
        }catch(\Exception $e){
            return view('errors.sql')
            ->with('title', 'Erro ao deletar categoria')
            ->with('motivo', 'Não é possivel remover categorias, com produtos incluidos!');
        }
    }


    private function _validate(Request $request){
        $rules = [
            'nome' => 'required|max:50'
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }
}
