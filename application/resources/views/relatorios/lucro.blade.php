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
			<h3 class="center-align">Relátorio de Lucro</h3>
			@if($data_inicial && $data_final)
			<h4>Periodo: {{$data_inicial}} - {{$data_final}}</h4>
			@endif
		</div>

		<table class="pure-table">
			<thead>
				<tr>
					<th width="110">DATA</th>
					<th width="110">LUCRO PDV</th>
					<th width="110">LUCRO VENDA NF-e</th>
					<th width="110">SOMA</th>
				</tr>
			</thead>

			

			<tbody>
				<?php $somaLucro = 0; ?>
				@foreach($lucros as $key => $v)
				<tr class="@if($key%2 == 0) pure-table-odd @endif">
					<td>{{$v['data']}}</td>
					<td>{{number_format($v['valor_caixa'], 2)}}</td>
					<td>{{number_format($v['valor'], 2)}}</td>
					<td>{{number_format($v['valor'] + $v['valor_caixa'], 2)}}</td>
				</tr>

				<?php $somaLucro += $v['valor'] + $v['valor_caixa'] ?>
				@endforeach
			</tbody>
		</table>
		<h4>Total lucro: <strong style="color: green">R$ {{number_format($somaLucro, 2)}}</strong></h4>


	</div>
	<div class="row">
		<canvas id="grafico-vendas" style="width: 100%; margin-left: 100px; margin-top: 20px;"></canvas>
	</div>

</body>
</html>