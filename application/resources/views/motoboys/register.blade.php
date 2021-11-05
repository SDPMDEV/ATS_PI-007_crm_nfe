@extends('default.layout')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

	<div class="container">
		<div class="card card-custom gutter-b example example-compact">
			<div class="col-lg-12">
				<!--begin::Portlet-->
				<form method="post" action="{{{ isset($motoboy) ? '/motoboys/update': '/motoboys/save' }}}">

					<input type="hidden" name="id" value="{{{ isset($motoboy->id) ? $motoboy->id : 0 }}}">

					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">{{isset($motoboy) ? 'Editar' : 'Novo'}} Motoboy</h3>
						</div>

					</div>
					@csrf

					<div class="row">
						<div class="col-xl-2"></div>
						<div class="col-xl-8">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row">
										<div class="form-group validated col-sm-8 col-lg-8">
											<label class="col-form-label">Nome</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" name="nome" value="{{{ isset($motoboy) ? $motoboy->nome : old('nome') }}}">
												@if($errors->has('nome'))
												<div class="invalid-feedback">
													{{ $errors->first('nome') }}
												</div>
												@endif
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="form-group validated col-sm-10 col-lg-10">
											<label class="col-form-label">Endereço</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('endereco')) is-invalid @endif" name="endereco" value="{{{ isset($motoboy) ? $motoboy->endereco : old('endereco') }}}">
												@if($errors->has('endereco'))
												<div class="invalid-feedback">
													{{ $errors->first('endereco') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Telefone 1</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('telefone1')) is-invalid @endif telefone" name="telefone1" value="{{{ isset($motoboy) ? $motoboy->telefone1 : old('telefone1') }}}">
												@if($errors->has('telefone1'))
												<div class="invalid-feedback">
													{{ $errors->first('telefone1') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Telefone 2</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('telefone2')) is-invalid @endif telefone" name="telefone2" value="{{{ isset($motoboy) ? $motoboy->telefone2 : old('telefone2') }}}">
												@if($errors->has('telefone2'))
												<div class="invalid-feedback">
													{{ $errors->first('telefone2') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-3 col-lg-3">
											<label class="col-form-label">Telefone 3</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('telefone3')) is-invalid @endif telefone" name="telefone3" value="{{{ isset($motoboy) ? $motoboy->telefone3 : old('telefone3') }}}">
												@if($errors->has('telefone3'))
												<div class="invalid-feedback">
													{{ $errors->first('telefone3') }}
												</div>
												@endif
											</div>
										</div>
									
									</div>

									<div class="row">
										<div class="form-group validated col-sm-4 col-lg-4">
											<label class="col-form-label">CPF</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('cpf')) is-invalid @endif" name="cpf" id="cpf" value="{{{ isset($motoboy) ? $motoboy->cpf : old('cpf') }}}">
												@if($errors->has('cpf'))
												<div class="invalid-feedback">
													{{ $errors->first('cpf') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-4">
											<label class="col-form-label">RG</label>
											<div class="">
												<input type="text" class="form-control @if($errors->has('rg')) is-invalid @endif" name="rg" id="rg" value="{{{ isset($motoboy) ? $motoboy->rg : old('rg') }}}">
												@if($errors->has('rg'))
												<div class="invalid-feedback">
													{{ $errors->first('rg') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-6 col-lg-6">
											<label class="col-form-label">Tipo de transporte</label>
											<div class="">
												<select class="form-control custom-select" name="tipo_transporte">
													@foreach(\App\Motoboy::tiposTransporte() as $tp)
													<option value="{{$tp}}">{{$tp}}</option>
													@endforeach
												</select>
												@if($errors->has('cpf'))
												<div class="invalid-feedback">
													{{ $errors->first('cpf') }}
												</div>
												@endif
											</div>
										</div>
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
								<a style="width: 100%" class="btn btn-danger" href="/bairrosDelivery">
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