<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ContaReceber;
use App\CategoriaConta;
use App\Usuario;

class ContaReceberController extends Controller
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
		$contas = ContaReceber::
		whereBetween('data_vencimento', [date("Y-m-d"), 
			date('Y-m-d', strtotime('+1 month'))])
		->get();

		$usuarios = Usuario::all();
		$somaContas = $this->somaCategoriaDeContas($contas);
		return view('contaReceber/list')
		->with('contas', $contas)
		->with('graficoJs', true)
		->with('somaContas', $somaContas)
		->with('usuarios', $usuarios)
		->with('infoDados', "Dos próximos 30 dias")
		->with('title', 'Contas a Receber');
	}

	private function somaCategoriaDeContas($contas){
		$arrayCategorias = $this->criaArrayDecategoriaDeContas();
		$temp = [];
		foreach($contas as $c){
			foreach($arrayCategorias as $a){
				if($c->categoria->nome == $a){
					if(isset($temp[$a])){
						$temp[$a] = $temp[$a] + $c->valor_integral;
					}else{
						$temp[$a] = $c->valor_integral;
					}
				}
			}
		}

		return $temp;
	}

	private function criaArrayDecategoriaDeContas(){
		$categorias = CategoriaConta::all();
		$temp = [];
		foreach($categorias as $c){
			array_push($temp, $c->nome);
		}

		return $temp;
	}

	public function filtro(Request $request){

		$dataInicial = $request->data_inicial;
		$dataFinal = $request->data_final;
		$cliente = $request->cliente;
		$status = $request->status;
		$usuario = $request->usuario;


		$contas = null;

		if(isset($cliente) && isset($dataInicial) && isset($dataFinal)){
			$contas = ContaReceber::filtroDataFornecedor(
				$cliente, 
				$this->parseDate($dataInicial),
				$this->parseDate($dataFinal),
				$status,
				$usuario
			);
		}else if(isset($dataInicial) && isset($dataFinal)){
			$contas = ContaReceber::filtroData(
				$this->parseDate($dataInicial),
				$this->parseDate($dataFinal),
				$status,
				$usuario
			);
		}else if(isset($cliente)){
			$contas = ContaReceber::filtroFornecedor(
				$cliente,
				$status,
				$usuario
			);
		}else if(isset($usuario)){
			$contas = ContaReceber::filtroUsuario(
				$usuario
			);
		}else{
			$contas = ContaReceber::filtroStatus($status);
		}

		$somaContas = $this->somaCategoriaDeContas($contas);
		$usuarios = Usuario::all();

		return view('contaReceber/list')
		->with('contas', $contas)
		->with('cliente', $cliente)
		->with('usuarios', $usuarios)
		->with('dataInicial', $dataInicial)
		->with('dataFinal', $dataFinal)
		->with('status', $status)
		->with('somaContas', $somaContas)
		->with('graficoJs', true)
		->with('infoDados', "Contas filtradas")
		->with('title', 'Filtro Contas a Receber');
	}

	public function salvarParcela(Request $request){
		$parcela = $request->parcela;

		$valorParcela = str_replace(".", "", $parcela['valor_parcela']);
		$valorParcela = str_replace(",", ".", $valorParcela);

		$result = ContaReceber::create([
			'venda_id' => $parcela['compra_id'],
			'data_vencimento' => $this->parseDate($parcela['vencimento']),
			'data_recebimento' => $this->parseDate($parcela['vencimento']),
			'valor_integral' => $valorParcela,
			'valor_recebido' => 0,
			'status' => false,
			'referencia' => $parcela['referencia'],
			'categoria_id' => 1,
		]);
		echo json_encode($parcela);
	}

	public function save(Request $request){
		
		if(strlen($request->recorrencia) == 5){
			echo $request->recorrencia;
			$valid = $this->validaRecorrencia($request->recorrencia);
			if(!$valid){
				session()->flash('mensagem_erro', 'Valor recorrente inválido!');
				return redirect('/contasReceber/new');
			}
		}


		$this->_validate($request);
		$result = ContaReceber::create([
			'venda_id' => null,
			'data_vencimento' => $this->parseDate($request->vencimento),
			'data_recebimento' => $this->parseDate($request->vencimento),
			'valor_integral' => str_replace(",", ".", $request->valor),
			'valor_recebido' => 0,
			'status' => $request->status ? true : false,
			'referencia' => $request->referencia,
			'categoria_id' => $request->categoria_id,
			'usuario_id' => get_id_user()
		]);
		
		$loopRecorrencia = $this->calculaRecorrencia($request->recorrencia);
		if($loopRecorrencia > 0){
			$diaVencimento = substr($request->vencimento, 0, 2);
			$proximoMes = substr($request->vencimento, 3, 2);
			$ano = substr($request->vencimento, 6, 4);

			while($loopRecorrencia > 0){
				$proximoMes = $proximoMes == 12 ? 1 : $proximoMes+1;
				$proximoMes = $proximoMes < 10 ? "0".$proximoMes : $proximoMes;
				if($proximoMes == 1)  $ano++;
				$d = $diaVencimento . "/".$proximoMes . "/" . $ano;

				$result = ContaReceber::create([
					'venda_id' => null,
					'data_vencimento' => $this->parseDate($d),
					'data_recebimento' => $this->parseDate($d),
					'valor_integral' => str_replace(",", ".", $request->valor),
					'valor_recebido' => 0,
					'status' => false,
					'referencia' => $request->referencia,
					'categoria_id' => $request->categoria_id
				]);
				$loopRecorrencia--;
			}
		}

		session()->flash('mensagem_sucesso', 'Registro inserido!');

		return redirect('/contasReceber');
	}

	public function update(Request $request){
		$this->_validate($request);
		$conta = ContaReceber::
		where('id', $request->id)
		->first();

		$conta->data_vencimento = $this->parseDate($request->vencimento);
		$conta->referencia = $request->referencia;
		$conta->valor_integral = str_replace(",", ".", $request->valor);
		$conta->categoria_id = $request->categoria_id;

		$result = $conta->save();

		if($result){

			session()->flash('mensagem_sucesso', 'Registro atualizado!');
		}else{

			session()->flash('mensagem_erro', 'Ocorreu um erro!');
		}

		return redirect('/contasReceber');

	}

	private function calculaRecorrencia($recorrencia){
		if(strlen($recorrencia) == 5){
			$dataAtual = date("Y-m");
			$dif = strtotime($this->parseRecorrencia($recorrencia)) - strtotime($dataAtual);

			$meses = floor($dif / (60 * 60 * 24 * 30));

			return $meses;
		}
		return 0;
	}

	public function validaRecorrencia($rec){
		$mesAutal = date('m');
		$anoAtual = date('y');
		$temp = explode("/", $rec);

		if($anoAtual > $temp[1]) return false;
		if((int)$temp[0] <= $mesAutal && $anoAtual == $temp[1]) return false;

		return true;
	}

	private function _validate(Request $request){
		$rules = [
			'referencia' => 'required',
			'valor' => 'required',
			'vencimento' => 'required',
		];

		$messages = [
			'referencia.required' => 'O campo referencia é obrigatório.',
			'valor.required' => 'O campo valor é obrigatório.',
			'vencimento.required' => 'O campo vencimento é obrigatório.'
		];
		$this->validate($request, $rules, $messages);
	}

	public function new(){
		$categorias = CategoriaConta::all();
		return view('contaReceber/register')
		->with('categorias', $categorias)
		->with('title', 'Cadastrar Contas a Receber');
	}

	public function edit($id){
		$categorias = CategoriaConta::all();
		$conta = ContaReceber::
		where('id', $id)
		->first();

		return view('contaReceber/register')
		->with('conta', $conta)
		->with('categorias', $categorias)
		->with('title', 'Editar Contas a Pagar');
	}

	public function receber($id){
		$categorias = CategoriaConta::all();
		$conta = ContaReceber::
		where('id', $id)
		->first();

		return view('contaReceber/receber')
		->with('conta', $conta)
		->with('categorias', $categorias)
		->with('title', 'Receber Conta');
	}

	public function receberConta(Request $request){
		$conta = ContaReceber::
		where('id', $request->id)
		->first();

		// $valor = str_replace(".", "", $request->valor);
		// $valor = str_replace(",", ".", $valor);

		if($conta->valor_integral != $request->valor){
			$valor = $request->valor;

			$contasParaReceber = ContaReceber::
			select('conta_recebers.*')
			->join('vendas', 'vendas.id' , '=', 'conta_recebers.venda_id')

			->where('conta_recebers.status', false)
			->where('conta_recebers.id', '!=', $conta->id)
			->where('vendas.cliente_id', $conta->venda->cliente_id)
			->get();

			if($conta->valor_integral > $request->valor){
				$contasParaReceber = [];
			}


			return view('contaReceber/valorDivergente')
			->with('conta', $conta)
			->with('valor', $valor)
			->with('receberConta', true)
			->with('contasParaReceber', $contasParaReceber)
			->with('title', 'Receber Conta');

		}else{

			$conta->status = true;
			$conta->valor_recebido = $request->valor;
			$conta->data_recebimento = date("Y-m-d");

			$result = $conta->save();
			if($result){

				session()->flash('mensagem_sucesso', 'Conta recebida!');
			}else{

				session()->flash('mensagem_erro', 'Erro!');
			}
			return redirect('/contasReceber');
		}
	}

	public function receberSomente(Request $request){
		$conta = ContaReceber::find($request->id);
		$valor = $request->valor;

		$conta->status = true;
		$conta->valor_recebido = $request->valor;
		$conta->data_recebimento = date("Y-m-d");

		$result = $conta->save();
		if($result){

			session()->flash('mensagem_sucesso', 'Conta recebida!');
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/contasReceber');
	}

	public function receberComDivergencia(Request $request){
		$conta = ContaReceber::find($request->id);
		$valor = $request->valor;

		$res = ContaReceber::create([
			'venda_id' => $conta->venda_id,
			'data_vencimento' => $conta->data_vencimento,
			'data_recebimento' => $conta->data_recebimento,
			'valor_integral' => $conta->valor_integral - $valor,
			'valor_recebido' => 0,
			'status' => false,
			'referencia' => $conta->referencia,
			'categoria_id' => 1,
			'usuario_id' => get_id_user()
		]);

		$conta->status = true;
		$conta->valor_recebido = $request->valor;
		$conta->valor_integral = $request->valor;
		$conta->data_recebimento = date("Y-m-d");

		$result = $conta->save();
		if($result){
			$id = $res->id;
			session()->flash('mensagem_sucesso', 'Conta recebida parcialmente, uma nova foi criada com ID: ' . $id);
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/contasReceber');
	}

	public function receberComOutros(Request $request){
		$conta = ContaReceber::find($request->id);
		$valor = $request->valor;
		$temp = "";
		$somaParaTroco = $conta->valor_integral;
		try{
			if(isset($request->contas)){
				$contasMais = explode(",", $request->contas);
				print_r($contasMais);
				foreach($contasMais as $key => $c){
					$ctemp = ContaReceber::find($c);
					$ctemp->status = true;
					$ctemp->valor_recebido = $ctemp->valor_integral;
					$ctemp->data_recebimento = date("Y-m-d");
					$ctemp->save();

					$temp .= " $c" . (sizeof($contasMais)-1 > $key ? "," : "");

					$somaParaTroco += $ctemp->valor_integral;
				}


			}

			$conta->status = true;
			$conta->valor_recebido = $conta->valor_integral;
			$conta->data_recebimento = date("Y-m-d");
			$conta->save();

			$troco = $valor - $somaParaTroco;

			$msg = "Sucesso conta(s) com ID: $conta->id, " . $temp . " recebida(s)";

			if($troco > 0){
				$msg .= " , valor de troco: R$ " . number_format($troco, 2);
			}
			session()->flash('mensagem_sucesso', $msg);

			return redirect('/contasReceber');

		}catch(\Exception $e){
			session()->flash('mensagem_erro', 'Ocorreu um erro ao receber: ' . $e->getMessage());

		}

	}

	public function delete($id){
		$delete = ContaReceber
		::where('id', $id)
		->delete();
		if($delete){

			session()->flash('mensagem_sucesso', 'Registro removido!');
		}else{

			session()->flash('mensagem_erro', 'Erro!');
		}
		return redirect('/contasReceber');
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	private function parseRecorrencia($rec){
		$temp = explode("/", $rec);
		$rec = "01/".$temp[0]."/20".$temp[1];
		//echo $rec;
		return date('Y-m', strtotime(str_replace("/", "-", $rec)));
	}
}
