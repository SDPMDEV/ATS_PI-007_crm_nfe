@extends('default.layout')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

	<div class="container">
		<div class="card card-custom gutter-b example example-compact">
			<div class="col-lg-12">
				<!--begin::Portlet-->

				<form method="post" action="/usuarios/{{{ isset($usuario) ? 'update' : 'save' }}}">

					<input type="hidden" name="id" value="{{{ isset($usuario) ? $usuario->id : 0 }}}">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title">{{isset($usuarios) ? 'Editar' : 'Nova'}} Usuário</h3>
						</div>

					</div>
					@csrf

					<div class="row">
						<div class="col-xl-2"></div>
						<div class="col-xl-8">
							<div class="kt-section kt-section--first">
								<div class="kt-section__body">


									<div class="row">
										<div class="form-group validated col-sm-10 col-lg-6">
											<label class="col-form-label">Nome</label>
											<div class="">
												<input id="nome" type="text" class="form-control @if($errors->has('nome')) is-invalid @endif" name="nome" value="{{{ isset($usuario) ? $usuario->nome : old('nome') }}}">
												@if($errors->has('nome'))
												<div class="invalid-feedback">
													{{ $errors->first('nome') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-10 col-lg-6">
											<label class="col-form-label">Login</label>
											<div class="">
												<input id="login" type="text" class="form-control @if($errors->has('login')) is-invalid @endif" name="login" value="{{{ isset($usuario) ? $usuario->login : old('login') }}}">
												@if($errors->has('login'))
												<div class="invalid-feedback">
													{{ $errors->first('login') }}
												</div>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="form-group validated col-sm-10 col-lg-6">
											<label class="col-form-label">Senha</label>
											<div class="">
												<input id="senha" type="password" class="form-control @if($errors->has('senha')) is-invalid @endif" name="senha" value="{{{ isset($usuario) ? '' : old('senha') }}}">
												@if($errors->has('senha'))
												<div class="invalid-feedback">
													{{ $errors->first('senha') }}
												</div>
												@endif
											</div>
										</div>

										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="col-form-label text-left col-lg-12 col-sm-12">ADM</label>
											<div class="col-6">
												<span class="switch switch-outline switch-primary">
													<label>
														<input id="adm" @if(isset($usuario->adm) && $usuario->adm) checked @endisset
														name="adm" type="checkbox" >
														<span></span>
													</label>
												</span>

											</div>
										</div>
									</div>

									<div class="row">

										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_cliente) checked @endisset
												id="acesso_cliente" name="acesso_cliente" />
												<span></span>
												Acesso Cliente
											</label>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_fornecedor) checked @endisset
												id="acesso_fornecedor" name="acesso_fornecedor" />
												<span></span>
												Acesso Fornecedor
											</label>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_produto) checked @endisset
												id="acesso_produto" name="acesso_produto" />
												<span></span>
												Acesso Produto
											</label>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_financeiro) checked @endisset
												id="acesso_financeiro" name="acesso_financeiro" />
												<span></span>
												Acesso Financeiro
											</label>
										</div>

										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_caixa) checked @endisset
												id="acesso_caixa" name="acesso_caixa" />
												<span></span>
												Acesso Caixa
											</label>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_estoque) checked @endisset
												id="acesso_estoque" name="acesso_estoque" />
												<span></span>
												Acesso Estoque
											</label>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_compra) checked @endisset
												id="acesso_compra" name="acesso_compra" />
												<span></span>
												Acesso Compra
											</label>
										</div>
										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->acesso_fiscal) checked @endisset
												id="acesso_fiscal" name="acesso_fiscal" />
												<span></span>
												Acesso Fiscal
											</label>
										</div>

										<div class="form-group validated col-sm-6 col-lg-3">
											<label class="checkbox">
												<input type="checkbox" @if(isset($usuario) && $usuario->venda_nao_fiscal) checked @endisset
												id="venda_nao_fiscal" name="venda_nao_fiscal" />
												<span></span>
												Venda não fiscal
											</label>
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
							<a style="width: 100%" class="btn btn-danger" href="/usuarios">
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



@endsection