@extends('delivery.default')
@section('content')

<style type="text/css">

.pulsate {
	-webkit-animation: pulsate 3s ease-out;
	-webkit-animation-iteration-count: infinite; 
	opacity: 0.5;
}
@-webkit-keyframes pulsate {
	0% { 
		opacity: 0.5;
	}
	50% { 
		opacity: 1.0;
	}
	100% { 
		opacity: 0.5;
	}
}
#map{
	width: 100%;
	height: 500px;
	background: #999;
}

</style>



<div class="row" id="anime" style="display: none;">
	<div class="col s8 offset-s2">
		<lottie-player 
		src="/anime/finish{{rand(1,4)}}.json" background="transparent"  speed="0.8"  style="width: 100%; height: auto;"    autoplay >
	</lottie-player>
</div>
</div>


<div class="row" id="content" style="display: block;">
	<div class="container ">
		<div class="title-section text-center">
			<h3 class="w3ls-title mb-3">Total do Pedido <span>{{number_format($total, 2, ',', '.')}}</span></h3>
		</div>
	</div>


	<section class="blog_w3ls py-5">
		<div class="container pb-xl-5 pb-lg-3">
			<div class="title-section text-center mb-md-5 mb-4">
				<h3 class="w3ls-title mb-3">Forma de Pagamento</h3>

			</div>
			<div class="container">

				<fieldset class="form-group">
					<div class="row">
						<div class="col-sm-12">
							<div class="form-check">

								<input class="form-check-input" type="radio" name="gridRadios" id="credito" value="credito">
								<label class="form-check-label" for="credito">
									Cartão de Crédito
								</label>
								<img width="40" src="/imgs/credit-card.png">

							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="gridRadios" id="debito" value="debito">
								<label class="form-check-label" for="debito">
									Cartão de Débito
								</label>
								<img width="40" src="/imgs/debit-card.png">

							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="gridRadios" id="dinheiro" value="dinheiro">
								<label class="form-check-label" for="dinheiro">
									Dinheiro
								</label>
								<img width="50" src="/imgs/100-real.png">

								<div id="div_do_troco" style="display: none" class="form-group">
									<label class="mb-2">Troco Para</label>
									<div class="col-sm-3">
										<input type="text" placeholder="Ex:  {{ $total%10 == 0 ? $total : ((int)($total/10) +1)*10}},00" class="form-control" id="troco_para">

									</div>
								</div>
							</div>

						</div>
					</div>
				</fieldset>

			</div>

		</div>
	</section>

	<section class="blog_w3ls">
		<div class="container">
			<div class="title-section text-center mb-md-5 mb-4">
				<h3 class="w3ls-title mb-3">Forma de Entrega</h3>
				<p>Seus Endereços Cadastrados</p>
			</div>
			<input type="hidden" id="lat_padrao" value="{{getenv('LATITUDE_PADRAO')}}">
			<input type="hidden" id="lng_padrao" value="{{getenv('LONGITUDE_PADRAO')}}">

			@if(count($enderecos) == 0)
			<p style="margin-left: 10px;" class="text-warning">Você ainda não possui endereços cadastrados</p><br>

			@endif
			<div class="row ends">

				@foreach($enderecos as $e)

				<div class="col-lg-4 col-md-6" onclick="set_endereco({{$e->id}})">
					<div id="endereco_select_{{$e->id}}" class="card border-0 med-blog">

						<div class="card-body border border-top-0">
							<h5 class="blog-title card-title m-0">
								{{$e->rua}}, {{$e->numero}}
							</h5>
							<h5>{{$e->bairro}}</h5>
							<p>Referencia: {{$e->referencia}}</p>
						</div>
					</div>
				</div>

				@endforeach

				<div class="col-lg-4 col-md-6" onclick="set_endereco('balcao')">
					<div id="endereco_select_balcao" class="card border-0 med-blog">

						<div class="card-body border border-top-0">
							<h5 class="blog-title card-title m-0">
								Retirar no Balcão
							</h5>

						</div>
					</div>
				</div>
			</div>
			<div class="container"><br>
				<a href="#gal2" id="novo-endereco" class="btn btn-success btn-lg">Novo Endereço</a>

			</div>


		</div>
	</section>

	

	<div class="container">
		<div id="acrescimo-entrega" style="display: none" class="form-group"><br>
			<h2>Acrescimo de entrega R$ <strong id="valor-entrega" style="color: red">
				
			</strong></h2>
		</div>
		<div class="form-group">
			<div class="col-lg-4 col-md-6 col-10">
				<label class="mb-2">Telefone de contato</label>
				<input type="text" class="form-control" id="telefone" required="">
			</div>
		</div>

		<div class="form-group">
			<div class="col-lg-12 col-md-12 col-12">
				<label class="mb-2">Observação do Pedido (opcional)</label>
				<input type="text" class="form-control" id="observacao" required="">
			</div>
		</div>

		<br>
		<div class="form-group">
			<div class="col-lg-4 col-md-4 col-8">
				<label style="color: red" class="mb-2">Cupom de Desconto (opcional)</label>
				<input type="text" class="form-control" id="cupom" value="{{$cupom>0 ? $cupom : ''}}" required="">
			</div>
		</div>

		<div id="desconto" style="display: none" class="col-lg-4 col-md-4 col-8">
			<h4>Valor do cupom: <strong style="color: green" id="valor-cupom"></strong></h4><br>
		</div>

		<div id="cupom-invalido" style="display: none" class="col-lg-4 col-md-4 col-8">
			<h4 style="color: red">Cupom inválido</h4><br>
		</div>
	</div>

	<input type="hidden" id="pedido_id" value="{{$pedido->id}}">
	<input type="hidden" id="total-init" value="{{$total}}">

	<a href="#!" type="button" id="finalizar-venda" class="btn btn-success btn-lg btn-block">
		<span class="fa fa-check mr-2"></span> FINALIZAR <strong id="total"></strong>
	</a>
	<br>
</div>

<div id="gal2" class="pop-overlay">
	<div style="width: 100%" class="popup">

		<div id="info-mapa">
			<p>Deslize o pino até sua localização!</p>
			<div id="map">	
			</div>
			<a style="color: #fff" id="btn-end-map" class="btn btn-success btn-block mb-4">Pronto</a>
		</div>

		<div id="form-endereco" style="display: none">
			
			<a style="color: #fff" id="abrir-mapa" class="btn btn-success btn-block mb-4">Abrir Mapa</a>

			<form>

				<input type="hidden" id="_token" value="{{ csrf_token() }}">
				<input type="hidden" id="cliente_id" value="{{$cliente->id}}">
				<div class="form-group">
					<label>Rua</label>
					<input type="text" class="form-control fr" id="rua" placeholder="" required="">
				</div>

				<div class="form-group">
					<label class="mb-2">Numero</label>
					<input type="text" class="form-control fr" id="numero" required="true">
				</div>

				<div class="form-group">
					<label class="mb-2">Bairro</label>
					<input type="text" class="form-control fr" id="bairro" required="true">
				</div>

				<div class="form-group">
					<label class="mb-2">Referencia</label>
					<input type="text" class="form-control fr" id="referencia" required="">
				</div>
				<a href="#!" id="salvar_endereco" class="btn btn-danger btn-block mb-4 disabled">Salvar</a>

			</form>
		</div>
		<a class="close" href="#!">×</a>
	</div>
</div>


@endsection	
