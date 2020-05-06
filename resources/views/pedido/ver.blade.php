@extends('default.layout')
@section('content')

<div class="row">


	<div class="col s12">
		@if(session()->has('message'))
		<div class="row">
			<div style="border-radius: 10px;" class="col s12 {{ session('color') }}">
				<h5 class="center-align white-text">{{ session()->get('message') }}</h5>
			</div>
		</div>
		@endif

		<h3>Comanda: {{$pedido->comanda}}</h3>

		@if($pedido->observacao != '')
		<h5>Observação: <strong>{{$pedido->observacao}}</strong></h5>
		@endif
		<form class="col s12" method="post" action="/pedidos/saveItem">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<input type="hidden" name="id" value="{{$pedido->id}}">
			<br>
			<div class="row">
				<div class="row">

					<div class="input-field col s4">
						<i class="material-icons prefix">inbox</i>
						<input autocomplete="off" type="text" value="{{old('produto')}}" name="produto" id="autocomplete-produto" class="autocomplete-produto">
						<label for="autocomplete-produto">Produto</label>
						@if($errors->has('produto'))
						<div class="center-align red lighten-2">
							<span class="white-text">{{ $errors->first('produto') }}</span>
						</div>
						@endif
					</div>
				</div>
				
				<div style="display: none" id="tamanhos-pizza">
					<div class="row">
						<div class="col s4">
							<i class="material-icons prefix">local_pizza</i>

							<div class="chips" id="tamanhos">

							</div>
							<label>Tamanhos de Pizza</label>
							@if($errors->has('tamanho_pizza_id'))
							<div class="center-align red lighten-2">
								<span class="white-text">{{ $errors->first('tamanho_pizza_id') }}</span>
							</div>
							@endif
						</div>
					</div>
				</div>

				
				<div style="display: block" id="sabores-pizza">
					<div class="row">
						<div class="col s4">
							<i class="material-icons prefix">local_pizza</i>

							<div id="sabores" class="chips chips-autocomplete">
							</div>
							<label>Sabores adicionais</label>
						</div>
					</div>
				</div>
			</div>

			<input type="hidden" name="tamanho_pizza_id" id="tamanho_pizza_id">
			<input type="hidden" name="sabores_escolhidos" id="sabores_escolhidos">
			<input type="hidden" name="adicioanis_escolhidos" id="adicioanis_escolhidos">



			<div class="row">
				<div class="col s4">
					<i class="material-icons prefix">room_service</i>

					<div class="chips chips-autocomplete" id="adicionais">
					</div>
					<label>Adicionais para este produto</label>
				</div>
			</div>

			<div class="row">
				<div class="input-field col s6">
					<i class="material-icons prefix">note</i>
					<input type="text" id="observacao" name="observacao">
					<label>Observação</label>
				</div>
			</div>



			<div class="row">
				<div class="input-field col s2">
					<i class="material-icons prefix">exposure_plus_1</i>
					<input type="text" value="1000" id="quantidade" name="quantidade">
					<label>Quantidade</label>
					@if($errors->has('quantidade'))
					<div class="center-align red lighten-2">
						<span class="white-text">{{ $errors->first('quantidade') }}</span>
					</div>
					@endif
				</div>

				<div class="input-field col s2">
					<i class="material-icons prefix">attach_money</i>
					<input type="text" value="0" id="valor" name="valor">
					<label>Valor</label>
					@if($errors->has('valor'))
					<div class="center-align red lighten-2">
						<span class="white-text">{{ $errors->first('valor') }}</span>
					</div>
					@endif
				</div>


				<div class="col s2">
					<button type="submit" class="btn-large green accent-3">
						<i class="material-icons">add</i>
					</button>
				</div>
			</div>
		</div>
	</form>

	<table class="striped col s12">
		<thead>
			<tr>
				<th>#</th>
				<th>Produto</th>
				<th>Tamanho de Pizza</th>
				<th>Sabores</th>
				<th>Adicionais</th>
				<th>Status</th>
				<th>Valor</th>
				<th>Quantidade</th>
				<th>Subtotal+adicional</th>
				<th>Observação</th>
				<th>Ações</th>
			</tr>
		</thead>

		<?php $finalizado = 0; $pendente = 0; ?>
		<tbody>
			@foreach($pedido->itens as $i)
			<tr>
				<?php $temp = $i; ?>
				<td>{{$i->produto_id}}</td>

				<td>{{$i->produto->nome}}</td>


				@if(!empty($i->tamanho))
				<td>{{$i->tamanho->nome}}</td>
				@else
				<td>--</td>
				@endif


				@if(count($i->sabores) > 0)
				<td>
					@foreach($i->sabores as $key => $s)
					{{$s->produto->produto->nome}}
					@if($key < count($i->sabores)-1)
					| 
					@endif
					@endforeach
				</td>
				@else
				<td>--</td>
				@endif

				<?php $somaAdicionais = 0; ?>
				@if(count($i->itensAdicionais) > 0)
				<td>
					@foreach($i->itensAdicionais as $key => $a)
					{{$a->adicional->nome}}
					<?php $somaAdicionais += $a->adicional->valor * $i->quantidade?>
					@if($key < count($i->itensAdicionais)-1)
					| 
					@endif
					@endforeach
				</td>
				@else
				<td>--</td>
				@endif

				<td>
					@if($i->status)
					<i class="material-icons green-text">brightness_1</i>
					@else
					<i class="material-icons red-text">brightness_1</i>
					@endif
				</td>

				<?php 
				$valorVenda = 0;

				// if(count($i->sabores) > 0){
				// 	$maiorValor = 0;
				// 	foreach($i->sabores as $s){
				// 		$v = $s->maiorValor($s->sabor_id, $i->tamanho_pizza_id);
				// 		if($v > $maiorValor) $maiorValor = $v;
				// 	}
				// 	$valorVenda = $maiorValor;
				// }else if(isset($i->produto->produto) && $i->produto->produto->valor_venda > 0){
				// 	$valorVenda = $i->produto->produto->valor_venda;
				// }else{
				// 	$valorVenda = $i->produto->valor_venda;
				// }


				$valorVenda = $i->valor;

				?>

				<td>{{number_format($valorVenda, 2, ',', '.')}}</td>

				<td>{{$temp->quantidade}}</td>
				<td>{{number_format((($valorVenda * $i->quantidade) + $somaAdicionais), 2, ',', '.')}}</td>
				<td>
					<a class="btn red lighten-2 tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{$i->observacao}}"
						@if(empty($i->observacao))
						disabled
						@endif
						>
						<i class="material-icons">message</i>

					</a>
				</td>
				<td>
					<a onclick = "if (! confirm('Deseja excluir este registro?')) { return false; }" href="/pedidos/deleteItem/{{$i->id}}">
						<i class="material-icons left red-text">delete</i>					
					</a>
					@if(!$i->status)
					<a href="/pedidos/alterarStatus/{{$i->id}}">
						<i class="material-icons green-text">check</i>
					</a>
					@endif
				</td>

				<?php 
				if($i->status) $finalizado++;
				else $pendente++;
				?>
			</tr>
			@endforeach
		</tbody>
	</table>


	<div class="row">
		<div class="col s12">
			<h4>TOTAL: <strong class="green-text">{{number_format($pedido->somaItems(), 2, ',', '.')}}</strong></h4>

			<h5>ITENS FINALIZADOS: <strong class="green-text">{{$finalizado}}</strong></h5>
			<h5>ITENS PENDENTES: <strong class="red-text">{{$pendente}}</strong></h5>
		</div>
		<br>

		<div class="row">
			<!-- <div class="col s3">
				<a href="#modal1" class="btn blue modal-trigger" style="width: 100%">Enviar SMS</a>
			</div>

			<div class="col s3">
				<a href="#modal2" class="btn green accent-3 modal-trigger" style="width: 100%">Enviar WhatsApp</a>
			</div> -->

			@if(count($pedido->itens) > 0)
			<div class="col s3">
				<a href="/pedidos/imprimirPedido/{{$pedido->id}}" target="_blank" class="btn brown" style="width: 100%">Imprimir pedido</a>
			</div>
			@endif

		</div>

		<div class="row">
			<div class="col s12">

				<a class="btn-large @if($pendente > 0 || $pedido->status) disabled @endif green accent-4" href="/pedidos/finalizar/{{$pedido->id}}">Finalizar</a>
			</div>
		</div>
		<input type="hidden" id="_token" value="{{csrf_token()}}">
	</div>
</div>
</div>


</div>

<div id="modal1" class="modal">
	<div class="modal-content">
		<h4>Envio de SMS</h4>

		<div class="row">
			<div class="input-field col s6">
				<input type="text" id="numero_sms">
				<label for="correcao">Numero</label>
			</div>
		</div>

		<div class="row">
			<div class="input-field col s12">
				<textarea id="msg_sms" class="materialize-textarea"></textarea>
				<label for="msg_sms">Mensagem</label>
			</div>
		</div>

		<div class="row" id="preloader1" style="display: none">
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
	</div>
	<div class="modal-footer">
		<a href="#!" class="modal-action modal-close btn grey">Fechar</a>
		<button onclick="sendSms()" class="btn blue">enviar</button>
	</div>
</div>

<div id="modal2" class="modal">

	<div class="modal-content">

		<div class="row">
			<div class="input-field col s4">
				<input type="text" id="numero_whats">
				<label>WhatsApp</label>
			</div>
		</div>

		<div class="row">
			<div class="input-field col s12">
				<input type="text" id="msg_whats">
				<label>Texto</label>
			</div>
		</div>

	</div>
	<div class="modal-footer">
		<a href="#!" onclick="enviarWhatsApp()" class="btn modal-action waves-effect waves-green green">Enviar</a>
		<a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Fechar</a>
	</div>
</div>

@endsection	