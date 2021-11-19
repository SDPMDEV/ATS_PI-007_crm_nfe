@extends('default.layout')
@section('content')

<div class="card card-custom gutter-b">
	<div class="card-body">
		<div class="">

			<div class="col-sm-12 col-lg-4 col-md-6 col-xl-4">

				<a href="/bannerMaisVendido/new" class="btn btn-lg btn-success">
					<i class="fa fa-plus"></i>
					Novo Banner	
				</a>
			</div>
		</div>
		<br>
		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
			<br>
			<h4>Lista de Banners mais vendidos</h4>

			<div class="row">

				@foreach($banners as $b)


				<div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
					<!--begin::Card-->
					<div class="card card-custom gutter-b card-stretch">
						<!--begin::Body-->
						<div class="card-body pt-4">
							<!--begin::Toolbar-->
							<div class="d-flex justify-content-end">
								<div class="dropdown dropdown-inline" data-toggle="tooltip" title="" data-placement="left" >
									<a href="#" class="btn btn-clean btn-hover-light-primary btn-sm btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="fa fa-ellipsis-h"></i>
									</a>
									<div class="dropdown-menu dropdown-menu-md dropdown-menu-right">
										<!--begin::Navigation-->
										<ul class="navi navi-hover">
											<li class="navi-header font-weight-bold py-4">
												<span class="font-size-lg">Ações:</span>
												
											</li>
											<li class="navi-separator mb-3 opacity-70"></li>

											<li class="navi-item">
												<a href="/bannerMaisVendido/edit/{{ $b->id }}" class="navi-link">
													<span class="navi-text">
														<span class="label label-xl label-inline label-light-primary">Editar</span>
													</span>
												</a>
											</li>
											<li class="navi-item">
												<a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/bannerMaisVendido/delete/{{ $b->id }}" }else{return false} })' href="#!" class="navi-link">
													<span class="navi-text">
														<span class="label label-xl label-inline label-light-danger">Remover</span>
													</span>
												</a>
											</li>
											
										</ul>
										<!--end::Navigation-->
									</div>
								</div>
							</div>
							<!--end::Toolbar-->
							<!--begin::User-->
							<div class="d-flex align-items-end mb-7">
								<!--begin::Pic-->
								<div class="d-flex align-items-center">
									<!--begin::Pic-->
									<div class="flex-shrink-0 mr-4 mt-lg-0 mt-3">
										<div class="symbol symbol-circle symbol-lg-75">
											
											@if($b->path)
											<img src="/banner_mais_vendido/{{$b->path}}" alt="image">
											@else
											<img src="/imgs/no_image.png" alt="image">
											@endif
										</div>
										<div class="symbol symbol-lg-75 symbol-circle symbol-primary d-none">
											<span class="font-size-h3 font-weight-boldest">JM</span>
										</div>
									</div>
									<!--end::Pic-->
									<!--begin::Title-->
									<div class="d-flex flex-column">

									</div>
									<!--end::Title-->
								</div>
								<!--end::Title-->
							</div>
							<!--end::User-->
							<!--begin::Desc-->
							<div class="mb-7">
								{{$b->status}}

								<div class="d-flex justify-content-between align-items-center">
									<span class="text-dark-75 font-weight-bolder mr-2">Ativo:</span>
									@if($b->ativo)
									<span class="label label-xl label-inline label-light-success">Ativo</span>
									@else
									<span class="label label-xl label-inline label-light-danger">Desativado</span>
									@endif


								</div>
								
								<div class="d-flex justify-content-between align-items-center">
									<span class="text-dark-75 font-weight-bolder mr-2">Produto:</span>
									@if($b->produto)
									<span class="text-danger">{{$b->produto->produto->nome}}</span>
									@else
									<span class="text-danger">--</span>
									@endif
								</div>
								

								
							</div>

							<!--end::Info-->
							<a onclick='swal("", "{{$b->texto_primario}}", "info")' class="btn btn-block btn-sm btn-light-info font-weight-bolder text-uppercase py-4">Texto primário</a>
							<a onclick='swal("", "{{$b->texto_secundario}}", "info")' class="btn btn-block btn-sm btn-light-danger font-weight-bolder text-uppercase py-4">Texto secundário</a>
							
						</div>
						<!--end::Body-->
					</div>
					<!--end::Card-->
				</div>

				@endforeach
			</div>

		</div>
	</div>
</div>


<div class="row">
	<div class="col s12">

		<h4>Lista de Banners mais vendidos</h4>

		@if(session()->has('message'))
		<div style="border-radius: 10px;" class="col s12 {{ session('color') }}">
			<h5 class="center-align white-text">{{ session()->get('message') }}</h5>
		</div>
		@endif

		

		<div class="row"></div>
		<div class="row">

			<a href="/bannerMaisVendido/new" class="btn green accent-3">
				<i class="material-icons left">add</i>	
				Novo Banner	
			</a>
		</div>



		<div class="row">
			<div class="col s12">
				<label>Numero de registros: {{count($banners)}}</label>					
			</div>
			<table class="col s12">
				<thead>
					<tr>
						<th>#</th>
						<th>Texto Primário</th>
						<th>Texto Secundário</th>
						<th>Imagem</th>
						<th>Ativo</th>
						<th>Ações</th>
					</tr>
				</thead>

				<tbody>
					@foreach($banners as $b)
					<tr>
						<th>{{ $b->id }}</th>
						<th>{{ $b->texto_primario }}</th>
						<th>{{ $b->texto_secundario }}</th>

						<th>
							<img style="width: 80px;" src="/banner_mais_vendido/{{$b->path}}">
						</th>
						<th>
							@if($b->ativo)
							<i class="material-icons green-text">brightness_1</i>
							@else
							<i class="material-icons red-text">brightness_1</i>
							@endif
						</th>

						<th>
							<a href="/bannerMaisVendido/edit/{{ $b->id }}">
								<i class="material-icons left">edit</i>					
							</a>


							<a onclick = "if (! confirm('Deseja excluir este registro?')) { return false; }" href="/bannerMaisVendido/delete/{{ $b->id }}">
								<i class="material-icons left red-text">delete</i>					
							</a>
						</th>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection	