@extends('default.layout')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

	<div class="container">
		<div class="card card-custom gutter-b example example-compact">
			<div class="col-lg-12">
				<!--begin::Portlet-->

				<form method="post" action="{{{ isset($banner) ? '/bannerTopo/update': '/bannerTopo/save' }}}" enctype="multipart/form-data">
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
											<label class="col-form-label">Titulo</label>
											<div class="">
												<input type="text" id="titulo" class="form-control @if($errors->has('titulo')) is-invalid @endif" name="titulo" value="{{{ isset($banner->titulo) ? $banner->titulo : old('titulo') }}}">
												@if($errors->has('titulo'))
												<div class="invalid-feedback">
													{{ $errors->first('titulo') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-12 col-lg-12 col-12">
											<label class="col-form-label">Descrição</label>
											<div class="">
												<input type="text" id="descricao" class="form-control @if($errors->has('descricao')) is-invalid @endif" name="descricao" value="{{{ isset($banner->descricao) ? $banner->descricao : old('descricao') }}}">
												@if($errors->has('descricao'))
												<div class="invalid-feedback">
													{{ $errors->first('descricao') }}
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
												<div class="image-input-wrapper" @if(isset($banner) && file_exists('banner_topo/'.$banner->path)) style="background-image: url(/banner_topo/{{$banner->path}})" @else style="background-image: url(/imgs/no_image.png)" @endif></div>
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
											<span class="text-danger">Imagem 1920x730px</span>

											
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

@endsection