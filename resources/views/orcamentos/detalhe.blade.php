@extends('default.layout')
@section('content')


<div class="card card-custom gutter-b">


	<div class="card-body">
		<div class="content d-flex flex-column flex-column-fluid" id="kt_content" >

			<div class="row" id="anime" style="display: none">
				<div class="col s8 offset-s2">
					<lottie-player src="/anime/success.json" background="transparent" speed="0.8" style="width: 100%; height: 300px;" autoplay >
					</lottie-player>
				</div>
			</div>	
			<input type="hidden" id="produtos" value="{{json_encode($produtos)}}" name="">


			<div class="col-lg-12" id="content">
				<!--begin::Portlet-->

				<h3 class="card-title">Orçamento código: <strong>{{$orcamento->id}}</strong></h3>

				<div class="row">
					<div class="col-xl-12">

						<div class="kt-section kt-section--first">
							<div class="kt-section__body">

								<div class="row">
									<div class="col-lg-6 col-md-6 col-sm-6 col-12">
										<h4>Cliente: <strong class="text-danger">{{$orcamento->cliente->razao_social}}</strong></h4>
										<h5>CNPJ: <strong class="text-danger">{{$orcamento->cliente->cpf_cnpj}}</strong></h5>
										<h5>Data: <strong class="text-danger">{{ \Carbon\Carbon::parse($orcamento->created_at)->format('d/m/Y H:i:s')}}</strong></h5>
										<h5>Valor Total: <strong class="text-danger">{{ number_format($orcamento->valor_total, 2, ',', '.') }}</strong></h5>
										<h5>Cidade: <strong class="text-danger">{{ $orcamento->cliente->cidade->nome }} ({{ $orcamento->cliente->cidade->uf }})</strong></h5>
										<h5>Dias restantes para o vencimento: <strong class="text-danger">{{ $diasParaVencimento }}</strong></h5>

										<h4>Estado: 
											@if($orcamento->estado == 'NOVO')
											<strong class="text-info">NOVO</strong>
											@elseif($orcamento->estado == 'APROVADO')
											<strong class="text-success">APROVADO</strong>
											@else
											<strong class="text-danger">REPROVADO</strong>
											@endif
										</h4>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xl-12">

						<form class="row align-items-center" method="post" action="/orcamentoVenda/setValidade">
							@csrf
							<div class="form-group col-lg-2 col-md-4 col-sm-6">
								<label class="col-form-label">Data</label>
								<div class="">
									<div class="input-group date">
										<input type="text" @if($orcamento->estado != 'NOVO') disabled @endif   name="validade" class="form-control" value="{{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y')}}" id="kt_datepicker_3" />
										<div class="input-group-append">
											<span class="input-group-text">
												<i class="la la-calendar"></i>
											</span>
										</div>
									</div>
								</div>
							</div>

							<input type="hidden" name="orcamento_id" value="{{$orcamento->id}}">
							
							<div class="col col-lg-2 col-md-4 col-sm-6">
								<button style="margin-top: 10px;" @if($orcamento->estado != 'NOVO') disabled @endif id="addProd" class="btn btn-info">
									<i class="la la-check"></i>
								</button>
							</div>
						</form>
					</div>
				</div>

				<hr>

				<div class="row">
					<form class="row" method="post" action="/orcamentoVenda/addItem">
						@csrf

						<div class="col-xl-12">
							<div class="row align-items-center">
								<div class="form-group validated col-sm-6 col-lg-5 col-12">
									<label class="col-form-label" id="">Produto</label><br>
									<input type="hidden" name="orcamento_id" value="{{$orcamento->id}}">
									<select class="form-control select2" style="width: 100%" id="kt_select2_1" name="produto">
										<option value="null">Selecione o produto</option>
										@foreach($produtos as $p)
										<option value="{{$p->id}}">{{$p->id}} - {{$p->nome}}</option>
										@endforeach
									</select>
								</div>

								<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
									<label class="col-form-label">Quantidade</label>
									<div class="">
										<div class="input-group">
											<input type="text" name="quantidade" class="form-control" value="0" id="quantidade"/>
										</div>
									</div>
								</div>
								<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
									<label class="col-form-label">Valor Unitário</label>
									<div class="">
										<div class="input-group">
											<input type="text" name="valor" class="form-control money" value="0" id="valor"/>
										</div>
									</div>
								</div>

								<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
									<label class="col-form-label">Subtotal</label>
									<div class="">
										<div class="input-group">
											<input type="text" name="subtotal" class="form-control" value="0" id="subtotal"/>
										</div>
									</div>
								</div>
								<div class="col-lg-1 col-md-4 col-sm-6 col-6">
									<button type="submit" style="margin-top: 15px;" class="btn btn-light-success px-6 font-weight-bold">
										<i class="la la-plus"></i>
									</button>

								</div>

							</div>
						</div>
					</form>
				</div>

				<div class="row">
					<div class="col-xl-12">

						<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

							<table class="datatable-table" style="max-width: 100%; overflow: scroll">
								<thead class="datatable-head">
									<tr class="datatable-row" style="left: 0px;">
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 60px;">#</span></th>
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Produto</span></th>
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Quantidade</span></th>
										<th data-field="Country" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
										<th data-field="ShipDate" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Subtotal</span></th>


										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Ações</span></th>
									</tr>
								</thead>
								<tbody id="body" class="datatable-body">
									<?php $somaItens = 0; ?>
									@foreach($orcamento->itens as $i)

									<tr class="datatable-row">
										<td class="datatable-cell"><span class="codigo" style="width: 60px;">{{ $i->id }}</span>
										</td>
										<td class="datatable-cell"><span class="codigo" style="width: 200px;">{{ $i->produto->nome }}</span>
										</td>
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{ $i->quantidade }}</span>
										</td>
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{number_format($i->valor, 2, ',', '.')}}</span>
										</td>
										<td class="datatable-cell"><span class="codigo" style="width: 100px;">{{number_format($i->valor * $i->quantidade, 2, ',', '.')}}</span>
										</td>
										<td class="datatable-cell">
											<span class="codigo" style="width: 100px;">
												@if($orcamento->estado == 'NOVO') 

												<a href="/orcamentoVenda/deleteItem/{{$i->id}}" class="btn btn-danger">
													<i class="la la-trash"></i>
												</a>
												@endif
											</span>
										</td>
									</tr>
									<?php $somaItens+=  $i->valor * $i->quantidade?>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-lg-12 col-md-12 col-sm-12 col-12">

							<h3>Soma: <strong class="text-success">R$ {{number_format($somaItens, 2)}}</strong></h3>
						</div>
					</div>
				</div>

				<hr>

				<div class="row">
					<form class="row" method="post" action="/orcamentoVenda/addPag">
						@csrf
						<input type="hidden" name="orcamento_id" value="{{$orcamento->id}}">

						<div class="col-xl-12">
							<div class="row align-items-center">

								<div class="form-group col-lg-3 col-md-4 col-sm-6 col-6">
									<label class="col-form-label">Valor</label>
									<div class="">
										<div class="input-group">
											<input type="text" @if($orcamento->estado != 'NOVO') disabled @endif name="valor" class="form-control money" value="0" id="valor"/>
										</div>
									</div>
								</div>

								<div class="form-group col-lg-4 col-md-4 col-sm-6">
									<label class="col-form-label">Data de Vencimento</label>
									<div class="">
										
										<div class="input-group date">
											<input type="text" @if($orcamento->estado != 'NOVO') disabled @endif name="data" class="form-control" readonly  id="kt_datepicker_3" />
											<div class="input-group-append">
												<span class="input-group-text">
													<i class="la la-calendar"></i>
												</span>
											</div>
										</div>
									</div>
								</div>
								

								<div class="col-lg-1 col-md-4 col-sm-6 col-6">
									<button type="submir" style="margin-top: 15px;" class="btn btn-light-success px-6 font-weight-bold">
										<i class="la la-plus"></i>
									</button>

								</div>

							</div>
						</div>
					</form>
				</div>

				<div class="row">
					<div class="col-xl-12">

						<div id="kt_datatable" class="datatable datatable-bordered datatable-head-custom datatable-default datatable-primary datatable-loaded">

							<table class="datatable-table" style="max-width: 100%; overflow: scroll">
								<thead class="datatable-head">
									<tr class="datatable-row" style="left: 0px;">
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Vencimento</span></th>
										<th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 120px;">Valor</span></th>
										


										<th data-field="CompanyName" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Ações</span></th>
									</tr>
								</thead>
								@if(count($orcamento->duplicatas))
								<tbody id="body" class="datatable-body">
									<?php $soma = 0; ?>
									@foreach($orcamento->duplicatas as $dp)

									<tr class="datatable-row">
										<td class="datatable-cell"><span class="codigo" style="width: 120px;">{{ \Carbon\Carbon::parse($dp->vencimento)->format('d/m/Y')}}</span>
										</td>
										<td class="datatable-cell"><span class="codigo" style="width: 120px;">{{number_format($dp->valor, 2, ',', '.')}}</span>
										</td>

										<td class="datatable-cell">
											<span class="codigo" style="width: 100px;">
												@if($orcamento->estado == 'NOVO') 
												<a href="/orcamentoVenda/deleteParcela/{{$dp->id}}" class="btn btn-danger">
													<i class="la la-trash"></i>
												</a>
												@endif
											</span>
										</td>
									</tr>
									<?php $soma += $dp->valor; ?>
									@endforeach
								</tbody>

								@else

								<tbody id="body" class="datatable-body">
									<tr class="datatable-row">
									</tr>
								</tbody>

								@endif
							</table>
						</div>
					</div>

					
				</div>

				<div class="row" style="margin-top: 20px;">
					<div class="form-group col-lg-3 col-md-4 col-sm-6 col-6">
						<a style="width: 100%;" target="_blank" href="/orcamentoVenda/imprimir/{{$orcamento->id}}" class="btn btn-primary">
							<i class="la la-print"></i> Imprimir
						</a>
					</div>
					<div class="form-group col-lg-3 col-md-4 col-sm-6 col-6">
						<a style="width: 100%;" @if($orcamento->estado != 'NOVO') disabled @endif href="/orcamentoVenda/reprovar/{{$orcamento->id}}" class="btn btn-danger">
							<i class="la la-times"></i>
							Alterar para reprovado
						</a>
					</div>
				</div>
				<form method="get" action="/orcamentoVenda/enviarEmail">

					<div class="row align-items-center">
						<input type="hidden" name="id" value="{{$orcamento->id}}">
						<input type="hidden" name="redirect" value="true">
						<div class="form-group col-lg-4 col-md-6 col-sm-6 col-6">
							<label class="col-form-label">Email</label>
							<div class="">
								<div class="input-group">
									<input type="text" name="email" class="form-control" name="email" id="email"/>
								</div>
							</div>
						</div>

						<div class="col-lg-2 col-md-4 col-sm-6 col-6">
							<button type="submir" style="margin-top: 15px;" class="btn btn-light-success px-6 font-weight-bold">
								Enviar Email
							</button>

						</div>
					</div>
				</form>

				<hr>

				<form method="post" action="/orcamentoVenda/gerarVenda">

					@csrf
					<input type="hidden" name="orcamento_id" value="{{$orcamento->id}}">
					<h5>Frete</h5>
					<div class="row">

						<div class="form-group col-lg-4 col-md-4 col-sm-6">
							<label class="col-form-label">Tipo de frete</label>
							<div class="">
								<div class="input-group date">
									<select class="custom-select form-control" id="tipo_frete" name="tipo_frete">
										<option value="0">0 - Emitente</option>
										<option value="1">1 - Destinatário</option>
										<option value="2">2 - Terceiros</option>
										<option @if($orcamento->frete_id == null) selected @endif value="9">9 - Sem Frete</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
							<label class="col-form-label">Placa do veiculo</label>
							<div class="">
								<div class="input-group">
									<input type="text" name="placa" class="form-control" id="placa"/>
								</div>
							</div>
						</div>

						<div class="form-group col-lg-2 col-md-4 col-sm-6">
							<label class="col-form-label">UF</label>
							<div class="">
								<div class="input-group date">
									<select class="custom-select form-control" id="uf_placa" name="uf_placa">
										<option value="--">--</option>
										<option value="AC">AC</option>
										<option value="AL">AL</option>
										<option value="AM">AM</option>
										<option value="AP">AP</option>
										<option value="BA">BA</option>
										<option value="CE">CE</option>
										<option value="DF">DF</option>
										<option value="ES">ES</option>
										<option value="GO">GO</option>
										<option value="MA">MA</option>
										<option value="MG">MG</option>
										<option value="MS">MS</option>
										<option value="MT">MT</option>
										<option value="PA">PA</option>
										<option value="PB">PB</option>
										<option value="PE">PE</option>
										<option value="PI">PI</option>
										<option value="PR">PR</option>
										<option value="RJ">RJ</option>
										<option value="RN">RN</option>
										<option value="RS">RS</option>
										<option value="RO">RO</option>
										<option value="RR">RR</option>
										<option value="SC">SC</option>
										<option value="SE">SE</option>
										<option value="SP">SP</option>
										<option value="TO">TO</option>
									</select>
								</div>
							</div>
						</div>
						<div class="form-group col-lg-2 col-md-4 col-sm-6 col-6">
							<label class="col-form-label">Valor do frete</label>
							<div class="">
								<div class="input-group">
									<input type="text" name="valor_frete" class="form-control money" id="valor_frete"/>
								</div>
							</div>
						</div>

					</div>
					<div class="row">

						<div class="form-group col-lg-4 col-md-4 col-sm-6">
							<label class="col-form-label">Natureza de Operação</label>
							<div class="">
								<div class="input-group date">
									<select class="custom-select form-control" id="natureza" name="natureza">
										@foreach($naturezas as $n)
										<option @if($n->id == $orcamento->natureza_id) selected @endif value="{{$n->id}}">{{$n->natureza}}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>

					<button @if(!$orcamento->validaGerarVenda() || $orcamento->estado != 'NOVO') disabled @endif type="submit" class="btn btn-success">
						<i class="la la-check"></i>
						Gerar Venda
					</button>

				</form>

			</div>
		</div>
	</div>
</div>


@endsection	