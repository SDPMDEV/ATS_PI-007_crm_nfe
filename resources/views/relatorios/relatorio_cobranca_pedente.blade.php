<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
	<link rel="stylesheet" href="/css/materialize.min.css">

</head>
<body>

	<div class="row">
		<div class="col s12">
			<h3 class="center-align">Relátorio de cobrança pendente</h3>
			@if($data_inicial && $data_final)
			<h4>Periodo: {{$data_inicial}} - {{$data_final}}</h4>
			@endif

			<h4>Usuário: <strong>{{$usuario}}</strong></h4>
		</div>

		<table class="pure-table">
			<thead>
				<tr>
					<th width="110">CLIENTE</th>
					<th width="110">DATA REGISTRO</th>
					<th width="110">VENCIMENTO</th>
					<th width="110">VALOR</th>
				</tr>
			</thead>

			

			<tbody>
				<?php $soma = 0; ?>
				@foreach($contas as $key => $v)
				<tr class="@if($key%2 == 0) pure-table-odd @endif">
					@if($v->venda_id != NULL)
					<td>{{$v->venda->cliente->razao_social}}</td>
					@else
					<td>--</td>
					@endif
					<td>{{\Carbon\Carbon::parse($v->created_at)->format('d/m/Y')}}</td>
					<td>{{\Carbon\Carbon::parse($v->data_vencimento)->format('d/m/Y')}}</td>
					<td>{{number_format($v->valor_integral, 2)}}</td>
				</tr>

				<?php $soma += $v->valor_integral; ?>
				@endforeach
			</tbody>
		</table>
		<h4>SOMA: <strong style="color: green">R$ {{number_format($soma, 2)}}</strong></h4>


	</div>
	<div class="row">
		<canvas id="grafico-vendas" style="width: 100%; margin-left: 100px; margin-top: 20px;"></canvas>
	</div>

</body>
</html>