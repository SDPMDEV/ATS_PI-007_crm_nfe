@extends('default.layout')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

	<div class="container">
		<div class="card card-custom gutter-b example example-compact">
			<div class="col-lg-12">
				<!--begin::Portlet-->

				<form method="post" action="{{{ isset($banner) ? '/bannerMaisVendido/update': '/bannerMaisVendido/save' }}}" enctype="multipart/form-data">
					<input type="hidden" name="id" value="{{{ isset($banner->id) ? $banner->id : 0 }}}">


					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">{{isset($banner) ? 'Editar' : 'Novo'}} Banner do Topo</h3>
						</div>

					</div>
					@csrf

					<div class="row">
						<div class="col-xl-2"></div>
						<div class="col-xl-8">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">
									<p class="text-danger">*Para Delivery do tipo mercado</p>

									<div class="row">
										<div class="form-group validated col-sm-8 col-lg-8 col-12">
											<label class="col-form-label">Texto primário</label>
											<div class="">
												<input type="text" id="texto_primario" class="form-control @if($errors->has('texto_primario')) is-invalid @endif" name="texto_primario" value="{{{ isset($banner->texto_primario) ? $banner->texto_primario : old('texto_primario') }}}">
												@if($errors->has('texto_primario'))
												<div class="invalid-feedback">
													{{ $errors->first('texto_primario') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-12 col-lg-12 col-12">
											<label class="col-form-label">Texto Secundário</label>
											<div class="">
												<input type="text" id="texto_secundario" class="form-control @if($errors->has('texto_secundario')) is-invalid @endif" name="texto_secundario" value="{{{ isset($banner->texto_secundario) ? $banner->texto_secundario : old('texto_secundario') }}}">
												@if($errors->has('texto_secundario'))
												<div class="invalid-feedback">
													{{ $errors->first('texto_secundario') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-8 col-lg-8 col-12">
											<label class="col-form-label" id="">Produto de delivery</label><br>
											<select class="form-control select2" style="width: 100%" id="kt_select2_1" name="produto_delivery_id">
												<option value="null">--</option>
												@foreach($produtos as $p)
												<option 
												@if(isset($banner))
												@if($p->id == $banner->produto_delivery_id)
												selected
												@endif
												@endif
												value="{{$p->id}}">{{$p->id}} - {{$p->produto->nome}}</option>
												@endforeach
											</select>
											@if($errors->has('produto_delivery_id'))
											<div class="invalid-feedback">
												{{ $errors->first('produto_delivery_id') }}
											</div>
											@endif
										</div>
										<div class="form-group validated col-sm-4 col-lg-4 col-12">
											<p><br><br>
												<input 
												@if(isset($banner) && $banner->ativo) checked 
												@endif type="checkbox" id="ativo" name="ativo" />
												<label for="ativo">Ativo</label>
											</p>
										</div>
									</div>



									<div class="form-group row">
										<label class="col-xl-12 col-lg-12 col-form-label text-left">Imagem</label>
										<div class="col-lg-10 col-xl-6">


											<div class="image-input image-input-outline" id="kt_image_1">
												<div class="image-input-wrapper" @if(isset($banner) && file_exists('banner_mais_vendido/'.$banner->path)) style="background-image: url(/banner_mais_vendido/{{$banner->path}})" @else style="background-image: url(/imgs/no_image.png)" @endif></div>
												<label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
													<i class="fa fa-pencil icon-sm text-muted"></i>
													<input type="file" name="file" accept=".png, .jpg, .jpeg">


													<input type="hidden" name="profile_avatar_remove">
												</label>
												<span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="cancel" data-toggle="tooltip" title="" data-original-title="Cancel avatar">
													<i class="fa fa-close icon-xs text-muted"></i>
												</span>
											</div>

											<span class="form-text text-muted">.png, .jpg, .jpeg</span>
											<span class="text-danger">Imagem 570x715px</span>

										</div>

										@if($errors->has('file'))
										<p class="text-danger">{{ $errors->first('file') }}</p>
										@endif


									</div>

								</div>
							</div>
						</div>

					</div>

					<div class="card-footer">

						<div class="row">
							<div class="col-xl-2">

							</div>
							<div class="col-lg-3 col-sm-6 col-md-4">
								<a style="width: 100%" class="btn btn-danger" href="/bannerTopo">
									<i class="la la-close"></i>
									<span class="">Cancelar</span>
								</a>
							</div>
							<div class="col-lg-3 col-sm-6 col-md-4">
								<button style="width: 100%" type="submit" class="btn btn-success">
									<i class="la la-check"></i>
									<span class="">Salvar</span>
								</button>
							</div>

						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col s12">
		<h4>{{{ isset($banner) ? "Editar": "Cadastrar" }}} Banner </h4>
		<p class="red-text">*Para Delivery do tipo mercado</p>
		<form method="post" action="{{{ isset($banner) ? '/bannerMaisVendido/update': '/bannerMaisVendido/save' }}}" enctype="multipart/form-data">
			<input type="hidden" name="id" value="{{{ isset($banner->id) ? $banner->id : 0 }}}">

			<div class="row">
				<div class="input-field col s6">
					<input value="{{{ isset($banner->texto_primario) ? $banner->texto_primario : old('texto_primario') }}}" id="texto_primario" name="texto_primario" type="text" class="validate" data-length="20">
					<label for="texto_primario">Texto Primário</label>

					@if($errors->has('texto_primario'))
					<div class="center-align red lighten-2">
						<span class="white-text">{{ $errors->first('texto_primario') }}</span>
					</div>
					@endif
				</div>
			</div>

			<div class="row">
				<div class="input-field col s10">

					<input value="{{{ isset($banner->texto_secundario) ? $banner->texto_secundario : old('texto_secundario') }}}" id="texto_secundario" name="texto_secundario" type="text" class="validate" data-length="30">

					<label for="texto_secundario">Texto Secundário</label>

					@if($errors->has('texto_secundario'))
					<div class="center-align red lighten-2">
						<span class="white-text">{{ $errors->first('texto_secundario') }}</span>
					</div>
					@endif
				</div>				
			</div>

			<div class="row">
				<div class="col s6">
					<div class="file-field input-field">
						<div class="btn black">
							<span>Imagem 570x715px</span>
							<input value="{{{ isset($banner->path) ? $banner->path : old('path') }}}" name="file" accept=".png, .jpg, .jpeg" type="file">
						</div>
						<div class="file-path-wrapper">

							<input class="file-path validate" type="text">
						</div> 

						@if($errors->has('file'))
						<div class="center-align red lighten-2">
							<span class="white-text">{{ $errors->first('file') }}</span>
						</div>
						@endif
					</div>
				</div>
				<div class="col s6">
					@if(isset($banner))
					<img src="/banner_mais_vendido/{{$banner->path}}">
					<label>Imagem atual</label>
					@endif
				</div>
			</div>

			<div class="row">
				<div class="input-field col s4">
					<i class="material-icons prefix">inbox</i>
					<input autocomplete="off" type="text" name="produto" id="autocomplete-produto" value="{{{ isset($banner) && $banner->produto_delivery_id != null ? $banner->produto->id .' - '. $banner->produto->produto->nome : old('produto') }}}" class="autocomplete-produto">
					<label for="autocomplete-produto">Produto de Delivery</label>
					@if($errors->has('produto'))
					<div class="center-align red lighten-2">
						<span class="white-text">{{ $errors->first('produto') }}</span>
					</div>
					@endif
				</div>

				<div class="col s3"><br>
					<p>
						<input 
						@if(isset($banner) && $banner->ativo) checked 
						@endif type="checkbox" id="status" name="status" />
						<label for="status">Ativo</label>
					</p>
				</div>
			</div>

			@csrf


			<br>
			<div class="row">
				<a class="btn-large red lighten-2" href="/bannerMaisVendido">Cancelar</a>
				<input type="submit" value="Salvar" class="btn-large green accent-3">
			</div>
		</form>
	</div>
</div>
@endsection