@extends('default.layout')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

	<div class="container">
		<div class="card card-custom gutter-b example example-compact">
			<div class="col-lg-12">
				<!--begin::Portlet-->

				<form method="post" action="/configMercado/save">
					<input type="hidden" name="id" value="{{{ isset($config->id) ? $config->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">{{{ isset($config) ? "Editar": "Cadastrar" }}} Configuração de Mercado</h3>
						</div>

					</div>
					@csrf
					<div class="row">
						<div class="col-xl-2"></div>
						<div class="col-xl-8">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">

									<div class="row">
										<div class="form-group validated col-sm-6 col-lg-6 col-12">
											<label class="col-form-label">Email</label>
											<div class="">
												<input id="email" type="text" class="form-control @if($errors->has('email')) is-invalid @endif" name="email" value="{{{ isset($config) ? $config->email : old('email') }}}">
												@if($errors->has('email'))
												<div class="invalid-feedback">
													{{ $errors->first('email') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-12 col-lg-12 col-12">
											<label class="col-form-label">Descreva o Funcionamento</label>
											<div class="">
												<input id="funcionamento" type="text" class="form-control @if($errors->has('funcionamento')) is-invalid @endif" name="funcionamento" value="{{{ isset($config) ? $config->funcionamento : old('funcionamento') }}}">
												@if($errors->has('funcionamento'))
												<div class="invalid-feedback">
													{{ $errors->first('funcionamento') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-12 col-lg-12 col-12">
											<label class="col-form-label">Descrição Home Page</label>
											<div class="">
												<input id="descricao" type="text" class="form-control @if($errors->has('descricao')) is-invalid @endif" name="descricao" value="{{{ isset($config) ? $config->descricao : old('descricao') }}}">
												@if($errors->has('descricao'))
												<div class="invalid-feedback">
													{{ $errors->first('descricao') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-4 col-lg-4 col-12">
											<label class="col-form-label">Total de produtos</label>
											<div class="">
												<input id="total_de_produtos" type="text" class="form-control @if($errors->has('total_de_produtos')) is-invalid @endif" name="total_de_produtos" value="{{{ isset($config) ? $config->total_de_produtos : old('total_de_produtos') }}}">
												@if($errors->has('total_de_produtos'))
												<div class="invalid-feedback">
													{{ $errors->first('total_de_produtos') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-4 col-12">
											<label class="col-form-label">Total de Clientes</label>
											<div class="">
												<input id="total_de_clientes" type="text" class="form-control @if($errors->has('total_de_clientes')) is-invalid @endif" name="total_de_clientes" value="{{{ isset($config) ? $config->total_de_clientes : old('total_de_clientes') }}}">
												@if($errors->has('total_de_clientes'))
												<div class="invalid-feedback">
													{{ $errors->first('total_de_clientes') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-4 col-lg-4 col-12">
											<label class="col-form-label">Total de Funcionários</label>
											<div class="">
												<input id="total_de_funcionarios" type="text" class="form-control @if($errors->has('total_de_funcionarios')) is-invalid @endif" name="total_de_funcionarios" value="{{{ isset($config) ? $config->total_de_funcionarios : old('total_de_funcionarios') }}}">
												@if($errors->has('total_de_funcionarios'))
												<div class="invalid-feedback">
													{{ $errors->first('total_de_funcionarios') }}
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
								<a style="width: 100%" class="btn btn-danger" href="/">
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