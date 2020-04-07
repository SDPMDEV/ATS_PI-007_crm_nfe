@extends('delivery.default')
@section('content')

<div class="clearfix"></div>


<div class="main-banner-2">

</div>

<section class="portfolio py-5">
	<div class="container py-xl-5 py-lg-3">
		<div class="title-section text-center mb-md-5 mb-4">
			<h3 class="w3ls-title mb-3"><span>Pizzas</span></h3>
			<p class="titile-para-text mx-auto">Selecionado {{session('tamanho_pizza')['tamanho']}} - 
				{{session('tamanho_pizza')['sabores']}} {{session('tamanho_pizza')['sabores'] == 1 ? 
			'sabor' : 'sabores'}}</p>

		</div>
		<form action="/pizza/pesquisa" method="get">
			<div class="row">
				<div class="col-lg-12 form-group">
					<input type="text" class="form-control" name="pesquisa" placeholder="Pesquisar sabor" required="">
				</div>
			</div>
			<button type="submit" class="btn submit-contact-main"><span class="fa fa-search mr-2"></span> Buscar</button>
		</form>

		<?php $ativo = false; ?>
		<br><br>
		<div class="row">
			<div class="col-lg-12">
				<h3 class="w3ls-title mb-3">Selecione {{session('tamanho_pizza')['sabores'] == 1 ? 
					'o' : 'os'}} {{session('tamanho_pizza')['sabores'] == 1 ? 
				'sabor' : 'sabores'}}</h3>

				<p style="color: red">*Prevalecerá o preço do sabor com maior valor</p>
			</div>
		</div>

		<div class="row mt-4">

			@foreach($pizzas as $p)
			@if($p->produto->status)
			<div class="col-md-4" 
			onclick="select_pizza({{$p->produto}}, {{$p->produto->galeria}}, {{$p->produto->produto}})">
			@if(session('tamanho_pizza')['sabores'] > count($saboresIncluidos))

			
			<a href="#add-pizza">
				@else
				<a href="#!">
					@endif
					<div @if(session('tamanho_pizza')['sabores'] == count($saboresIncluidos))style="opacity: 0.3" @endif class="gallery-demo" id="pizza_{{$p->produto->id}}">
						@if(count($p->produto->galeria) > 0)
						<img src="/imagens_produtos/{{$p->produto->galeria[0]->path}}" 
						alt="" style="height: 200px; width: 100%" class="img-fluid" />
						@else
						<img src="https://www.strokejoinville.com.br/wp-content/uploads/sites/804/2017/05/produto-sem-imagem-Copia-1.gif" class="img-fluid" >
						@endif

						<h4 class="p-mask">{{$p->produto->produto->nome}} - 

							<span>R${{$p->valor}}</span>

						</h4>

					</div>
				</a>
			</div>
			<?php $ativo = true; ?>
			@endif
			@endforeach


		</div>

		<div class="title-section text-center mb-md-5 mb-4">
			@if(isset($pesquisa) && count($pizzas) == 0)
			<h3 class="w3ls-title mb-3 "><span>Nada encontrado!</span></h3>

			@else
			@if(!$ativo)
			<h4 class="w3ls-title mb-3 "><span>Esta categoria ainda não possui produtos :(</span></h4>
			@endif
			@endif
		</div>


		<br>
		@if(session('tamanho_pizza')['sabores'] == count($saboresIncluidos))
		<div class="row">
			<h3>Sabores escolhidos adicione ao carrinho!</h3>
		</div>
		
		@endif

	</div>


</section>





@if(count($saboresIncluidos) == 0)
<div class="container py-xl-5 py-lg-3">
	<div class="title-section text-center mb-md-5 mb-4">

	</div>
</div>
@else


<section class="blog_w3ls py-5" id="blog">

	<div class="container py-xl-5 py-lg-3">

		<div class="title-section text-center mb-md-5 mb-4">
			<h3 class="w3ls-title mb-3">Sabores Adicionados</h3>
			<p class="titile-para-text mx-auto">Este são os sabores adicionados,

				<span style="color: red">Fatando: {{session('tamanho_pizza')['sabores'] - count($saboresIncluidos)}}</span> para adicionar esta pizza ao carrinho
			</p>

		</div>

		<div class="row">

			@foreach($saboresIncluidos as $s)
			<div class="col-lg-4 col-md-6">
				<div class="card border-0">
					<div class="card-header p-0">
						<a href="single.html">
							@if(count($p->produto->galeria) > 0)
							<img src="/imagens_produtos/{{$p->produto->galeria[0]->path}}" 
							alt="" style="height: 200px; width: 100%" class="img-fluid" />
							@else
							<img src="/imgs/no_image.png" class="img-fluid" >
							@endif


						</a>

					</div>
					<div class="card-body text-center pt-5 mt-2">
						<h5 class="blog-title card-title mb-2"><a href="single.html">{{$s['produto']['nome']}}</a></h5>
						<div class="blog_w3icon border-top border-bottom py-1 mb-3">
							<span>
							{{$s['descricao']}}</span>
						</div>
						<p>{{$s['ingredientes']}}</p>
						<a href="/pizza/removeSabor/{{$s['id']}}" class="btn btn-danger btn-block mb-4">Remover
							<span class="fa fa-times" aria-hidden="true"></span>
						</a>
					</div>
				</div>
			</div>
			@endforeach

		</div>
	</div>
</section>

@endif



<a href="/pizza/adicionais" type="button" id="finalizar-venda" class="btn btn-success btn-lg btn-block @if(count($saboresIncluidos) < session('tamanho_pizza')['sabores'] )
disabled
@endif">
<span class="fa fa-check mr-2"></span> ADICIONAR<strong id="total"></strong>
</a>
<br>


<div id="add-pizza" class="pop-overlay active">
	<div class="popup">

		<form method="post" action="/pizza/adicionarSabor">
			@csrf
			<section class="" id="blog">
				<div class="">
					<div class="title-section text-center mb-md-5 mb-4">
						<h3 class="w3ls-title mb-3">Adicionar sabor a pizza</h3>

					</div>
					<div class="row">
						<!-- blog grid -->
						<div class="col-lg-12 col-md-12">
							<div class="card border-0">
								<div class="card-header p-0">
									<a href="single.html">
										<img src="" id="img" class="card-img-bottom img-fluid" alt="image">
									</a>

								</div>
								<div class="card-body text-center pt-5 mt-2">
									<h5 class="blog-title card-title mb-2" id="sabor"></h5>
									<div class="blog_w3icon border-top border-bottom py-1 mb-3">
										<span id="descricao"></span>
									</div>
									<p id="ingredientes"></p>

									<input type="hidden" id="pizza_id" name="pizza_id" value="">
									<button type="submit" class="btn btn-danger btn-block mb-4">Adicionar
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</form>


		<a class="close" href="#!">×</a>
	</div>
</div>

@endsection	

