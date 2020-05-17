@extends('default.layout')
@section('content')

<div class="row">
	<div class="col s12">

		<h4 class="center-align">DF-e</h4>
		<div class="row">
			<div class="input-field col s2">
				<input type="text" value="{{{isset($data_inicial) ? $data_inicial : ''}}}" id="data_inicial" class="datepicker">
				<label>Data Inicial</label>
			</div>
			<div class="input-field col s2">
				<input type="text" id="data_final" value="{{{ isset($data_final) ? $data_final : '' }}}" class="datepicker">
				<label>Data Inicial</label>
			</div>
			<div class="col s2">
				<button onclick="filtrar()" type="submit" class="btn-large black">
					<i class="material-icons">search</i>
				</button>
			</div>
			<div class="row" id="preloader" style="display: none">
				<div class="col s2 center-align">
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

		@if(session()->has('message'))
		<div class="row">
			<div style="border-radius: 10px;" class="col s12 {{ session('color') }}">
				<h5 class="center-align white-text">{{ session()->get('message') }}</h5>
			</div>
		</div>
		@endif

		<p>Total de Documentos: <strong id="total-documentos">0</strong></p>
		<div class="row">
			<table>
				<thead>
					<tr>
						<th>Nome</th>
						<th>Documento</th>
						<th>Valor</th>
						<th>Data Emissão</th>
						<th>Num. Protocolo</th>
						<th>Chave</th>
						<th>Ações</th>
					</tr>
				</thead>
				<tbody id="tbl">
					<tr>
						@foreach($docs as $d)
						<tr>
							<td>{{$d['nome']}}</td>
							<td>{{$d['cnpj']}}</td>
							<td>{{$d['valor']}}</td>

							<th>{{ \Carbon\Carbon::parse($d['data_emissao'])->format('d/m/Y H:i:s')}}</th>

							<td>{{$d['num_prot']}}</td>
							<td>{{$d['chave']}}</td>
							<td>

								@if(isset($d['incluso']))
								<a href="/dfe/download/{{$d['chave']}}" class="btn green">Completa</a>
								@else
								<form method="get" action="/dfe/manifestar">
									<input type="hidden" name="nome" value="{{$d['nome']}}">
									<input type="hidden" name="cnpj" value="{{$d['cnpj']}}">
									<input type="hidden" name="valor" value="{{$d['valor']}}">
									<input type="hidden" name="data_emissao" value="{{$d['data_emissao']}}">
									<input type="hidden" name="num_prot" value="{{$d['num_prot']}}">
									<input type="hidden" name="chave" value="{{$d['chave']}}">
									<button type="submit" class="btn red">Manifestar</button>
								</form>
								@endif
							</td>
						</tr>
						@endforeach
					</tr>
				</tbody>
			</table>
		</div>
		
	</div>
</div>


@endsection	