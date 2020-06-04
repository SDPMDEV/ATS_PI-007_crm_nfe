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


		<div class="card">
			<div class="row">
				<div class="col s6">
					<h4>Código: <strong>{{$compra->id}}</strong></h4>
					<h4>NF: <strong>{{$compra->nf ?? '*'}}</strong></h4>
					<h4>Usuário: <strong>{{$compra->usuario->nome}}</strong></h4>
					@if($compra->nf)
					<h6>Chave: <strong>{{$compra->chave}}</strong></h6>
					@endif

				</div>
				<div class="col s6">
					<h4>Fornecedor: <strong>{{$compra->fornecedor->razao_social}}</strong></h4>
					<h4>Data: <strong>{{ \Carbon\Carbon::parse($compra->date_register)->format('d/m/Y H:i:s')}}</strong></h4>
					<h5>Observação: {{$compra->observacao}}</h5>
				</div>
				
			</div>
		</div>
		
		

		<table class="striped col s12">
			<thead>
				<tr>
					<th>#</th>
					<th>Produto</th>
					<th>Valor</th>
					<th>Quantidade</th>
					<th>Subtotal</th>
					<th>Ações</th>
				</tr>
			</thead>

			<tbody>
				@foreach($compra->itens as $i)
				<tr>
					<td>{{$i->produto_id}}</td>
					<td>{{$i->produto->nome}}</td>
					<td>{{number_format($i->valor_unitario, 2, ',', '.')}}</td>
					<td>{{$i->quantidade}}</td>
					<td>{{number_format(($i->valor_unitario * $i->quantidade), 2, ',', '.')}}</td>
					<td>
						<a onclick = "if (! confirm('Deseja excluir este registro?')) { return false; }" href="/pedidos/deleteItem/{{ $i->id }}">
							<i class="material-icons left red-text">delete</i>					
						</a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		<input type="hidden" id="_token" value="{{csrf_token()}}">
		
		
		<div class="row">
			<div class="col s9">
				<h4>TOTAL: <strong class="green-text">{{number_format($compra->somaItems(), 2, ',', '.')}}</strong></h4>

			</div>
			<div class="col s3">
				<br>
				@if($compra->chave != '')
				<a target="_blank" style="width: 100%" href="/compras/downloadXml/{{$compra->id}}" class="btn">
					<i class="material-icons left">archive</i> Downlaod XML
				</a>
				@else
				<a target="_blank" onclick="enviar({{$compra->id}})" style="width: 100%" class="btn red">
					<i class="material-icons left">nfc</i> Transmitir
				</a>
				@endif
			</div>
		</div>
	</div>

	
</div>
@endsection	