@extends('default.layout')
@section('content')
<style type="text/css">
.table{
	height: 325px;
	overflow-x:auto;
}
table tbody{

}

.money-cel{
	width: 120px;
	height: 50px;
}

.money-moeda{
	width: 80px;
}
</style>
<nav>
	<div class="nav-wrapper">
		<ul class="right hide-on-med-and-down">
			<li><a><i class="material-icons left yellow-text">update</i> <strong id="horas">00:00</strong></a></li>
			<li><a><i class="material-icons left yellow-text">date_range</i> <strong id="data"></strong></a></li>

		</ul>

		<ul class="left hide-on-med-and-down">
			<li><a><i class="material-icons left black-text">person</i> {{ session('user_logged')['nome']}}</a></li>

		</ul>
	</div>
</nav>

<div class="row">
	<input type="hidden" id="_token" value="{{ csrf_token() }}">

	<div class="row">
		<div class="col s12 green">
			<h1 id="nome-produto" class="center-align white-text">--</h1>
		</div>
	</div>
	
	@if(isset($itens))
	<input type="hidden" id="itens_pedido" value="{{json_encode($itens)}}">
	<input type="hidden" id="delivery_id" @if(isset($delivery_id)) value="{{$delivery_id}}" @else value='0' @endif>

	@endif
	<div class="col s5">
		<div class="row">
			<div class="row">
				<div class="input-field col s10">
					<i class="material-icons prefix">person</i>
					<input autocomplete="off" type="text" name="cliente" id="autocomplete-cliente" class="autocomplete-cliente">
					<label for="autocomplete-cliente green-text">Cliente</label>

				</div>
				<div class="col s2"><br>
					<button style="display: none" id="edit-cliente" class="btn"><i class="material-icons">edit</i></button>
				</div>
				
				<div class="col s12">
					<h6 id="cliente-nao" class="red-text">*Sem cliente identificado</h6>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="row">
				
				<div class="row">
					<div class="input-field col s12">
						<i class="material-icons prefix">inbox</i>
						<input autocomplete="off" type="text" name="produto" id="autocomplete-produto" class="autocomplete-produto">
						<label for="autocomplete-produto green-text">Produto</label>

					</div>
				</div>

				<div class="row">
					<div class="input-field col s6">
						<i class="material-icons prefix">exposure_plus_1</i>
						<input type="text" id="quantidade" value="1">
						<label for="quantidade">Quantidade</label>

					</div>

					<div class="input-field col s6">
						<i class="material-icons prefix">attach_money</i>
						<input type="text" id="valor_item" value="0,00">
						<label for="valor_item">Valor do Item</label>

					</div>
				</div>

				<div class="row">
					<button id="adicionar-item" class="btn-large orange" style="width: 100%;">Adicionar Item</button>
				</div>


				<div class="row">

					<h5 class="center-align">ATALHOS</h5>
					<div class="row">
						<div class="col s6">
							<button style="width: 100%;" href="#modal2" class="btn-large modal-trigger grey lighten-1">Sangria</button>
						</div>
						<div class="col s6">
							<a href="/frenteCaixa/devolucao" style="width: 100%;" class="btn-large red lighten-1">Devolução</a>
						</div>
					</div>
					<div class="row">
						<div class="col s6">
							<button onclick="fluxoDiario()" style="width: 100%;" class="btn-large blue lighten-1">Fluxo Diário</button>
						</div>

						<div class="col s6">
							<a href="/frenteCaixa/list" style="width: 100%;" class="btn-large green lighten-1">Lista de Vendas</a>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		
	</div>
	<div class="col s7">
		<div class="card">
			<div class="row">
				<div class="col s12">
					
					<div class="card table">
						<div class="row">
							<div class="col s12">
								<h5>ITENS</h5>
							</div>
							<table class="stiped">
								<thead>
									<tr>
										<th>ITEM</th>
										<th>ID</th>
										<th>PRODUTO</th>
										<th>QTD</th>
										<th>V.UNIT</th>
										<th>SUB</th>
									</tr>
								</thead>
								<tbody id="body">

								</tbody>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col s3">
							<h6>Itens: <strong class="green-text" id="qtd-itens">0</strong></h6>
						</div>
						<div class="col s3">
							<h6>Quantidade: <strong class="green-text" id="_qtd">0</strong></h6>
						</div>
						<div class="col s5 offset-s1">
							<button style="width: 100%" onclick="verItens()" href="" class="btn">Ver todos os itens</button>
						</div>
						

					</div>

					<div class="row">
						<div class="col s6 input-field">
							<input type="text" id="valor_recebido" name="" value="">
							<label>Valor Recebido</label>
						</div>
						<div class="input-field col s6">
							<select id="tipo-pagamento">
								<option value="dinheiro">DINHEIRO</option>
								<option value="cartao">CARTÃO</option>
								
							</select>
							<label>Tipo de Pagamento</label>
						</div>

					</div>
					

					<div class="row">
						<button id="finalizar-venda" style="width: 100%;" class="btn-large green modal-trigger disabled" href="#modal-venda">Finalizar Venda</button>
					</div>
				</div>
			</div>
			

		</div>
	</div>
</div>

<div id="modal1" class="modal">
	<div class="modal-content">
		<h4>É necessário abrir o caixa com um valor</h4>
		<div class="row">
			<div class="col s6 input-field">
				<input type="text" id="valor" name="">
				<label>Valor</label>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#!" onclick="abrirCaixa()" class="btn">Abrir</a>
	</div>
</div>


<div id="modal2" class="modal">
	<div class="modal-content">
		<h4>Sangria de caixa</h4>
		<div class="row">
			<div class="col s10 input-field">
				<input type="text" id="valor_sangria" name="">
				<label>Valor</label>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#!" onclick="sangriaCaixa()" class="btn">Salvar</a>
	</div>
</div>

<div id="modal3" class="modal">
	<div class="modal-content">
		<h4>Fluxo Diário</h4>

		<div id="preloader1" style="display: none">
			<div class="col s12 center-align">
				<div class="preloader-wrapper active">
					<div class="spinner-layer spinner--only">
						<div class="circle-clipper left">
							<div class="circle"></div>
						</div><div class="gap-patch">
							<div class="circle"></div>
						</div><div class="circle-clipper right">
							<div class="circle"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="row">
				<div class="col s12">
					<h5>Abertura de Caixa:</h5>
					<div id="fluxo_abertura_caixa"></div>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<h5>Sangrias:</h5>
					<div id="fluxo_sangrias"></div>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<h5>Vendas:</h5>
					<div id="fluxo_vendas"></div>
				</div>
			</div>

			<div class="row">
				<div class="col s12">
					<h5>Total em caixa: 
						<strong id="total_caixa" class="green-text"></strong></h5>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Fechar</a>
		</div>
	</div>

	<div id="modal4" class="modal">
		<div class="modal-content">
			<div class="row">
				<h4>Valor do troco: <strong id="valor_troco" class="orange-text">0,00</strong></h4>

				<h5>Sugestão:</h5>
				<div class="row 50_reais" style="display: none">
					<div class="col s3">
						<img class="money-cel" src="/imgs/50_reais.jpeg"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_50_reais"></h4>
					</div>
				</div>
				<div class="row 20_reais" style="display: none">
					<div class="col s3">
						<img class="money-cel" src="/imgs/20_reais.jpeg"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_20_reais"></h4>
					</div>
				</div>

				<div class="row 10_reais" style="display: none">
					<div class="col s3">
						<img class="money-cel" src="/imgs/10_reais.jpeg"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_10_reais"></h4>
					</div>
				</div>

				<div class="row 5_reais" style="display: none">
					<div class="col s3">
						<img class="money-cel" src="/imgs/5_reais.jpeg"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_5_reais"></h4>
					</div>
				</div>

				<div class="row 2_reais" style="display: none">
					<div class="col s3">
						<img class="money-cel" src="/imgs/2_reais.jpeg"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_2_reais"></h4>
					</div>
				</div>

				<div class="row 1_real" style="display: none">
					<div class="col s3">
						<img class="money-moeda" src="/imgs/1_real.png"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_1_real"></h4>
					</div>
				</div>

				<div class="row 50_centavo" style="display: none">
					<div class="col s3">
						<img class="money-moeda" src="/imgs/50_centavo.png"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_50_centavos"></h4>
					</div>
				</div>

				<div class="row 25_centavo" style="display: none">
					<div class="col s3">
						<img class="money-moeda" src="/imgs/25_centavo.png"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_25_centavos"></h4>
					</div>
				</div>

				<div class="row 10_centavo" style="display: none">
					<div class="col s3">
						<img class="money-moeda" src="/imgs/10_centavo.png"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_10_centavos"></h4>
					</div>
				</div>


				<div class="row 5_centavo" style="display: none">
					<div class="col s3">
						<img class="money-moeda" src="/imgs/5_centavo.png"> 
					</div>
					<div class="col s3">
						<h4 id="qtd_5_centavos"></h4>
					</div>
				</div>

			</div>
		</div>
		<div class="modal-footer">
			<div class="modal-footer">
				<a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Fechar</a>
			</div>

		</div>
	</div>

	<div id="modal-credito" class="modal">

		<div class="modal-content">
			<p class="center-align"><i class="large material-icons green-text">check_circle</i></p>
			<h4 class="center-align">Tudo Certo!</h4>
			<p class="center-align" id="evento-conta-credito"></p>

		</div>
		<div class="modal-footer">
			<a href="#!" onclick="redireciona()" class="modal-action modal-close waves-effect waves-green btn-flat">Fechar</a>
		</div>
	</div>

	<div id="modal-alert" class="modal">

		<div class="modal-content">
			<p class="center-align"><i class="large material-icons green-text">check_circle</i></p>
			<h4 class="center-align">Tudo Certo!</h4>
			<p class="center-align" id="evento"></p>

		</div>
		<div class="modal-footer">
			<a href="#!" onclick="redireciona()" class="modal-action modal-close waves-effect waves-green btn-flat">Fechar</a>
		</div>
	</div>

	<div id="modal-alert-erro" class="modal">

		<div class="modal-content">
			<p class="center-align"><i class="large material-icons red-text">error</i></p>
			<h4 class="center-align">Aldo deu errado!</h4>
			<p class="center-align" id="evento-erro"></p>

		</div>
		<div class="modal-footer">
			<a href="#!" onclick="redireciona()" class="modal-action modal-close waves-effect waves-green btn-flat">Fechar</a>
		</div>
	</div>

	<div id="modal-venda" class="modal">
		<div class="modal-content">

			<div class="row">
				<div class="col s4">
					<a class="btn-large blue modal-trigger" style="width: 100%" onclick="verificaCliente()" class="btn-large green">Cupom Fiscal</a>
				</div>
				<div class="col s4">
					<button onclick="finalizarVenda('nao_fiscal')" style="width: 100%" class="btn-large red">Cupom Não Fiscal</button>
				</div>

				<div class="col s4">
					<button id="conta_credito-btn" onclick="finalizarVenda('credito')" style="width: 100%" class="btn-large orange disabled">Conta Crédito</button>
				</div>

				<div id="preloader9" class="row" style="display: none">
					<br>
					<div class="col s12 center-align">
						<div class="preloader-wrapper active">
							<div class="spinner-layer spinner--only">
								<div class="circle-clipper left">
									<div class="circle"></div>
								</div><div class="gap-patch">
									<div class="circle"></div>
								</div><div class="circle-clipper right">
									<div class="circle"></div>
								</div>
							</div>
						</div>
					</div>
					<p class="center-align">Emitindo NFCe</p>
				</div>
			</div>
		</div>
	</div>

	<div id="modal-cpf-nota" class="modal">
		<div class="modal-content">
			<h4 class="center-align">CPF NA NOTA?</h4>
			<div class="row">
				<div class="col s8">
					<input type="text" id="nome" name="">
					<label>Nome</label>
				</div>
			</div>

			<div class="row">
				<div class="col s6">
					<input type="text" id="cpf" name="">
					<label>CPF</label>
				</div>
			</div>

			<div id="preloader2" class="row" style="display: none">
				<div class="col s12 center-align">
					<div class="preloader-wrapper active">
						<div class="spinner-layer spinner--only">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>
					</div>
				</div>
				<p class="center-align">Emitindo NFCe</p>
			</div>

			<div class="col s6 ofsset-s3">
				<button onclick="finalizarVenda('fiscal')" style="width: 100%" class="btn-large green">OK</button>
				
				<!-- <button onclick="teste()" style="width: 100%" class="btn-large green">OK</button> -->
			</div>
		</div>
	</div>

	<div id="modal-itens" class="modal">
		<div class="modal-content">

			<table class="stiped">
				<thead>
					<tr>
						<th>ITEM</th>
						<th>ID</th>
						<th>NOME</th>
						<th>QTD</th>
						<th>V.UNIT</th>
						<th>SUB</th>
					</tr>
				</thead>
				<tbody id="body-modal">

				</tbody>
			</table>
		</div>
		<div class="modal-footer">
			<a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Fechar</a>
		</div>
	</div>

	@endsection