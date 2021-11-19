@extends('default.layout')
@section('content')
<style type="text/css">
	#map{
		width: 100%;
		height: 600px;
		background: #999;
		margin-bottom: 50px;
	}
</style>

<div class="card card-custom gutter-b">
	<div class="card-body">

		<div class="card card-custom gutter-b">


			<div class="card-body">
				<div class="row">
					<div class="col-12">

						<h2 class="text-info">{{$tipo}}</h2>

					</div>


					<div class="col-sm-6 col-lg-6 col-md-6 col-xl-6 col-12">
						<h5>Latitude Local: <strong>{{$config->latitude}}</strong></h5>
						<h5>Longitude Local: <strong>{{$config->longitude}}</strong></h5>

						<input type="hidden" value="{{$config->latitude}}" id="lat_local">
						<input type="hidden" value="{{$config->longitude}}" id="lng_local">

						<input type="hidden" value="{{json_encode($pedidosPendentes)}}" id="pedidosPendentes">
						<input type="hidden" value="{{json_encode($pedidosFinalizados)}}" id="pedidosFinalizados">

					</div>
					
					<div class="col-sm-6 col-lg-6 col-md-6 col-xl-6 col-12">
						<h4>Total de pedidos pendentes: <strong class="text-danger">{{sizeof($pedidosPendentes)}}</strong></h4>
						<h4>Total de pedidos finalizados: <strong class="text-success">
							{{sizeof($pedidosFinalizados)}}
						</strong></h4>
					</div>

					<div class="col-12">
						<div class="form-group validated col-sm-2 col-lg-4">
							<label class="col-form-label">Filtro</label>
							<div class="">
								<select id="select-filtro" class="form-control custom-select">
									<option value="pendentes">Pendentes</option>
									<option value="finalizados">Finalizados</option>
									<option value="ambos">Ambos</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div id="map"></div>
		</div>
	</div>
</div>

@endsection