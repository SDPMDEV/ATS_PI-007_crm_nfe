<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', 'HomeController@index');
// header('Access-Control-Allow-Origin', '*');



Route::group(['prefix' => '/'], function(){
	Route::get('/', 'DeliveryController@index');
});

Route::group(['prefix' => '/autenticar'], function(){
	Route::get('/', 'DeliveryController@login');
	Route::post('/', 'DeliveryController@autenticar');
	Route::get('/registro', 'DeliveryController@registro');
	Route::get('/logoff', 'DeliveryController@logoff');
	Route::get('/novo', 'DeliveryController@autenticarCliente');
	Route::post('/registro', 'DeliveryController@salvarRegistro');
	Route::get('/esqueceu_a_senha', 'DeliveryController@recuperarSenha');
	Route::post('/esqueceu_a_senha', 'DeliveryController@enviarSenhaEmail');
	Route::post('/validaToken', 'DeliveryController@validaToken');
	Route::get('/ativar/{cliente_id}', 'DeliveryController@ativar');
	Route::post('/refreshToken', 'DeliveryController@refreshToken');
	Route::get('/saveTokenWeb', 'DeliveryController@saveTokenWeb');

});

Route::group(['prefix' => '/cardapio'], function(){
	Route::get('/', 'DeliveryController@cardapio');
	Route::get('/{id}', 'DeliveryController@produtos');
	Route::get('/acompanhamento/{id}', 'DeliveryController@acompanhamento');

});

Route::group(['prefix' => '/pizza'], function(){
	Route::get('/escolherSabores', 'DeliveryController@escolherSabores');
	Route::post('/adicionarSabor', 'DeliveryController@adicionarSabor');
	Route::get('/verificaPizzaAdicionada', 'DeliveryController@verificaPizzaAdicionada');
	Route::get('/removeSabor/{id}', 'DeliveryController@removeSabor');
	Route::get('/adicionais', 'DeliveryController@adicionais');
	Route::get('/pesquisa', 'DeliveryController@pesquisa');
	Route::get('/pizzas', 'DeliveryController@pizzas');
	
});

Route::group(['prefix' => '/carrinho'], function(){
	Route::get('/', 'CarrinhoController@carrinho');
	Route::post('/add', 'CarrinhoController@add');
	Route::post('/addPizza', 'CarrinhoController@addPizza');
	Route::get('/removeItem/{id}', 'CarrinhoController@removeItem');
	Route::get('/refreshItem/{id}/{quantidade}', 'CarrinhoController@refreshItem');
	Route::get('/forma_pagamento/{cupom?}', 'CarrinhoController@forma_pagamento');
	Route::post('/finalizarPedido', 'CarrinhoController@finalizarPedido');
	Route::get('/historico', 'CarrinhoController@historico');
	Route::get('/pedir_novamente/{id}', 'CarrinhoController@pedir_novamente');
	Route::get('/finalizado/{id}', 'CarrinhoController@finalizado');
	Route::get('/configDelivery', 'CarrinhoController@configDelivery');
	Route::get('/cupons', 'CarrinhoController@cupons');
	Route::get('/cupom/{codigo}', 'CarrinhoController@cupom');
});

Route::group(['prefix' => '/enderecoDelivery'], function(){
	// Route::get('/{id}', 'EnderecoDeliveryController@index');
	Route::post('/save', 'EnderecoDeliveryController@save');

});

Route::group(['prefix' => '/pedidosDelivery'], function(){
	Route::get('/', 'PedidoDeliveryController@today');
	Route::get('/verPedido/{id}', 'PedidoDeliveryController@verPedido');
	Route::get('/filtro', 'PedidoDeliveryController@filtro');
	Route::get('/alterarStatus/{id}', 'PedidoDeliveryController@alterarStatus');
	Route::get('/irParaFrenteCaixa/{id}', 'PedidoDeliveryController@irParaFrenteCaixa');
	Route::get('/alterarPedido', 'PedidoDeliveryController@alterarPedido');
	Route::get('/confirmarAlteracao', 'PedidoDeliveryController@confirmarAlteracao');
	Route::get('/print/{id}', 'PedidoDeliveryController@print');
	Route::get('/verCarrinhos', 'PedidoDeliveryController@verCarrinhos');
	Route::get('/verCarrinho/{id}', 'PedidoDeliveryController@verCarrinho');
	Route::get('/push/{id}', 'PedidoDeliveryController@push');
	Route::get('/emAberto', 'PedidoDeliveryController@emAberto');
	Route::post('/sendPush', 'PedidoDeliveryController@sendPush');

});


Route::group(['prefix' => '/configDelivery'], function(){
	Route::get('/', 'ConfigDeliveryController@index');
	Route::post('/save', 'ConfigDeliveryController@save');
});

Route::group(['prefix' => 'deliveryCategoria'], function(){
	Route::get('/', 'DeliveryConfigCategoriaController@index');
	Route::get('/delete/{id}', 'DeliveryConfigCategoriaController@delete');
	Route::get('/edit/{id}', 'DeliveryConfigCategoriaController@edit');
	Route::get('/additional/{id}', 'DeliveryConfigCategoriaController@additional');
	Route::get('/removeAditional/{id}', 'DeliveryConfigCategoriaController@removeAditional');
	Route::post('/saveAditional', 'DeliveryConfigCategoriaController@saveAditional');
	Route::get('/new', 'DeliveryConfigCategoriaController@new');

	Route::post('/request', 'DeliveryConfigCategoriaController@request');
	Route::post('/save', 'DeliveryConfigCategoriaController@save');
	Route::post('/update', 'DeliveryConfigCategoriaController@update');
});

Route::group(['prefix' => 'deliveryComplemento'], function(){
	Route::get('/', 'DeliveryComplementoController@index');
	Route::get('/delete/{id}', 'DeliveryComplementoController@delete');
	Route::get('/edit/{id}', 'DeliveryComplementoController@edit');
	Route::get('/new', 'DeliveryComplementoController@new');
	Route::get('/all', 'DeliveryComplementoController@all');
	Route::get('/allPedidoLocal', 'DeliveryComplementoController@allPedidoLocal');

	Route::post('/request', 'DeliveryComplementoController@request');
	Route::post('/save', 'DeliveryComplementoController@save');
	Route::post('/update', 'DeliveryComplementoController@update');
});

Route::group(['prefix' => 'deliveryProduto'], function(){
	Route::get('/', 'DeliveryConfigProdutoController@index');
	Route::get('/delete/{id}', 'DeliveryConfigProdutoController@delete');
	Route::get('/deleteImagem/{id}', 'DeliveryConfigProdutoController@deleteImagem');
	Route::get('/edit/{id}', 'DeliveryConfigProdutoController@edit');
	Route::get('/galeria/{id}', 'DeliveryConfigProdutoController@galeria');
	Route::get('/push/{id}', 'DeliveryConfigProdutoController@push');
	Route::get('/new', 'DeliveryConfigProdutoController@new');

	Route::get('/alterarDestaque/{id}', 'DeliveryConfigProdutoController@alterarDestaque');
	Route::get('/alterarStatus/{id}', 'DeliveryConfigProdutoController@alterarStatus');

	Route::post('/request', 'DeliveryConfigProdutoController@request');
	Route::post('/save', 'DeliveryConfigProdutoController@save');
	Route::post('/saveImagem', 'DeliveryConfigProdutoController@saveImagem');
	Route::post('/update', 'DeliveryConfigProdutoController@update');
});


Route::group(['prefix' => 'configNF'], function(){
	Route::get('/', 'ConfigNotaController@index');
	Route::post('/save', 'ConfigNotaController@save');
	Route::get('/certificado', 'ConfigNotaController@certificado');
	Route::post('/certificado', 'ConfigNotaController@saveCertificado');


});

Route::group(['prefix' => 'escritorio'], function(){
	Route::get('/', 'EscritorioController@index');
	Route::post('/save', 'EscritorioController@save');
});

Route::group(['prefix' => 'aberturaCaixa'], function(){
	Route::get('/verificaHoje', 'AberturaCaixaController@verificaHoje');
	Route::post('/abrir', 'AberturaCaixaController@abrir');
	Route::get('/diaria', 'AberturaCaixaController@diaria');
});

Route::get('/app', 'PedidoRestController@apk');


Route::group(['prefix' => 'pedidos'], function(){
	Route::get('/', 'PedidoController@index');
	Route::post('/abrir', 'PedidoController@abrir');
	Route::get('/ver/{id}', 'PedidoController@ver');
	Route::get('/deleteItem/{id}', 'PedidoController@deleteItem');
	Route::get('/desativar/{id}', 'PedidoController@desativar');
	Route::get('/alterarStatus/{id}', 'PedidoController@alterarStatus');
	Route::get('/finalizar/{id}', 'PedidoController@finalizar');
	Route::get('/itensPendentes', 'PedidoController@itensPendentes');
	Route::post('/saveItem', 'PedidoController@saveItem');

	Route::get('/emAberto', 'PedidoController@emAberto');

	Route::post('/sms', 'PedidoController@sms');
	Route::get('/imprimirPedido/{id}', 'PedidoController@imprimirPedido');

});

Route::group(['prefix' => 'sangriaCaixa'], function(){
	Route::post('/save', 'SangriaCaixaController@save');
	Route::get('/diaria', 'SangriaCaixaController@diaria');
});

Route::group(['prefix' => 'cidades'], function(){
	Route::get('/all', 'CidadeController@all');
	Route::get('/find/{id}', 'CidadeController@find');
	Route::get('/findNome/{nome}', 'CidadeController@findNome');
});


Route::group(['prefix' => 'login'],function(){
	Route::get('/', 'UserController@newAccess');
	Route::get('/logoff', 'UserController@logoff');
	Route::post('/request', 'UserController@request');
});

Route::group(['prefix' => 'usuarios'],function(){
	Route::get('/', 'UsuarioController@lista');
	Route::get('/new', 'UsuarioController@new');
	Route::get('/edit/{id}', 'UsuarioController@edit');
	Route::post('/save', 'UsuarioController@save');
	Route::post('/update', 'UsuarioController@update');
});

Route::get('/sempermissao', function(){
	return view('sempermissao')->with('title', 'Acesso Bloqueado');
});

Route::group(['prefix' => 'categorias'],function(){
	Route::get('/', 'CategoryController@index');
	Route::get('/delete/{id}', 'CategoryController@delete');
	Route::get('/edit/{id}', 'CategoryController@edit');
	Route::get('/new', 'CategoryController@new');

	Route::post('/request', 'CategoryController@request');
	Route::post('/save', 'CategoryController@save');
	Route::post('/update', 'CategoryController@update');
});

Route::group(['prefix' => 'naturezaOperacao'],function(){
	Route::get('/', 'NaturezaOperacaoController@index');
	Route::get('/delete/{id}', 'NaturezaOperacaoController@delete');
	Route::get('/edit/{id}', 'NaturezaOperacaoController@edit');
	Route::get('/new', 'NaturezaOperacaoController@new');

	Route::post('/request', 'NaturezaOperacaoController@request');
	Route::post('/save', 'NaturezaOperacaoController@save');
	Route::post('/update', 'NaturezaOperacaoController@update');
});

Route::group(['prefix' => 'categoriasServico'],function(){
	Route::get('/', 'CategoriaServicoController@index');
	Route::get('/delete/{id}', 'CategoriaServicoController@delete');
	Route::get('/edit/{id}', 'CategoriaServicoController@edit');
	Route::get('/new', 'CategoriaServicoController@new');

	Route::post('/request', 'CategoriaServicoController@request');
	Route::post('/save', 'CategoriaServicoController@save');
	Route::post('/update', 'CategoriaServicoController@update');
	Route::post('/update', 'CategoriaServicoController@update');
});

Route::group(['prefix' => 'categoriasConta'],function(){
	Route::get('/', 'CategoriaContaController@index');
	Route::get('/delete/{id}', 'CategoriaContaController@delete');
	Route::get('/edit/{id}', 'CategoriaContaController@edit');
	Route::get('/new', 'CategoriaContaController@new');

	Route::post('/request', 'CategoriaContaController@request');
	Route::post('/save', 'CategoriaContaController@save');
	Route::post('/update', 'CategoriaContaController@update');
});


Route::group(['prefix' => 'contasPagar'],function(){
	Route::post('/salvarParcela', 'ContasPagarController@salvarParcela');
	Route::get('/', 'ContasPagarController@index');
	Route::get('/filtro', 'ContasPagarController@filtro');
	Route::get('/new', 'ContasPagarController@new');
	Route::get('/edit/{id}', 'ContasPagarController@edit');
	Route::get('/delete/{id}', 'ContasPagarController@delete');
	Route::get('/pagar/{id}', 'ContasPagarController@pagar');

	Route::post('/save', 'ContasPagarController@save');
	Route::post('/update', 'ContasPagarController@update');
	Route::post('/pagar', 'ContasPagarController@pagarConta');
});

Route::group(['prefix' => 'contasReceber'],function(){
	Route::post('/salvarParcela', 'ContaReceberController@salvarParcela');
	Route::get('/', 'ContaReceberController@index');
	Route::get('/filtro', 'ContaReceberController@filtro');
	Route::get('/new', 'ContaReceberController@new');
	Route::get('/edit/{id}', 'ContaReceberController@edit');
	Route::get('/delete/{id}', 'ContaReceberController@delete');
	Route::get('/receber/{id}', 'ContaReceberController@receber');

	Route::post('/save', 'ContaReceberController@save');
	Route::post('/update', 'ContaReceberController@update');
	Route::post('/receber', 'ContaReceberController@receberConta');
});


//

Route::group(['prefix' => 'produtos'],function(){
	Route::get('/', 'ProductController@index');
	Route::get('/delete/{id}', 'ProductController@delete');
	Route::get('/edit/{id}', 'ProductController@edit');
	Route::get('/new', 'ProductController@new');
	Route::get('/all', 'ProductController@all');
	Route::get('/composto', 'ProductController@composto');
	Route::get('/naoComposto', 'ProductController@naoComposto');
	Route::get('/getProduto/{id}', 'ProductController@getProduto');
	Route::get('/getProdutoCodBarras/{id}', 'ProductController@getProdutoCodBarras');
	Route::get('/receita/{id}', 'ProductController@receita');
	Route::get('/pesquisa', 'ProductController@pesquisa');
	Route::get('/filtroCategoria', 'ProductController@filtroCategoria');
	Route::get('/getUnidadesMedida', 'ProductController@getUnidadesMedida');

	Route::post('/request', 'ProductController@request');
	Route::post('/save', 'ProductController@save');
	Route::post('/update', 'ProductController@update');
	Route::post('/getValue', 'ProductController@getValue');
	Route::post('/salvarProdutoDaNota', 'ProductController@salvarProdutoDaNota');

});

Route::group(['prefix' => 'receita'],function(){
	Route::post('/save', 'ReceitaController@save');
	Route::post('/update', 'ReceitaController@update');
	Route::post('/saveItem', 'ReceitaController@saveItem');
	Route::get('/deleteItem/{id}', 'ReceitaController@deleteItem');

});

Route::group(['prefix' => 'vendasEmCredito'],function(){
	Route::get('/', 'CreditoVendaController@index');
	Route::get('/receber', 'CreditoVendaController@receber');
	Route::get('/receber', 'CreditoVendaController@receber');
	Route::get('/delete/{id}', 'CreditoVendaController@delete');
	Route::get('/somaVendas/{cliente_id}', 'CreditoVendaController@somaVendas');
	
	Route::get('/emitirNFe', 'CreditoVendaController@emitirNFe');
	Route::get('/apenasReceber', 'CreditoVendaController@apenasReceber');

});

Route::group(['prefix' => 'vendasCaixa'],function(){
	Route::post('/save', 'VendaCaixaController@save');
	Route::get('/diaria', 'VendaCaixaController@diaria');
});

Route::group(['prefix' => 'tributos'], function(){
	Route::get('/', 'TributoController@index');
	Route::post('/save', 'TributoController@save');
});

Route::group(['prefix' => 'funcionamentoDelivery'], function(){
	Route::get('/', 'FuncionamentoDeliveryController@index');
	Route::post('/save', 'FuncionamentoDeliveryController@save');
	Route::get('/edit/{id}', 'FuncionamentoDeliveryController@edit');
	Route::get('/alterarStatus/{id}', 'FuncionamentoDeliveryController@alterarStatus');

});


Route::group(['prefix' => 'enviarXml'],function(){
	Route::get('/', 'EnviarXmlController@index');
	Route::get('/filtro', 'EnviarXmlController@filtro');
	Route::get('/download', 'EnviarXmlController@download');
	Route::get('/downloadNfce', 'EnviarXmlController@downloadNfce');
	Route::get('/email/{d1}/{d2}', 'EnviarXmlController@email');
	Route::get('/emailNfce/{d1}/{d2}', 'EnviarXmlController@emailNfce');
	Route::get('/send', 'EnviarXmlController@send');
});

Route::group(['prefix' => 'nf'],function(){
	Route::post('/gerarNf', 'NotaFiscalController@gerarNf');
	Route::get('/gerarNf/{id}', 'NotaFiscalController@testeGerar');
	Route::get('/imprimir/{id}', 'NotaFiscalController@imprimir');
	Route::get('/escpos/{id}', 'NotaFiscalController@escpos');
	Route::get('/imprimirCce/{id}', 'NotaFiscalController@imprimirCce');
	Route::get('/imprimirCancela/{id}', 'NotaFiscalController@imprimirCancela');
	Route::get('/consultar_cliente/{id}', 'NotaFiscalController@consultar_cliente');
	Route::post('/cancelar', 'NotaFiscalController@cancelar');
	Route::post('/consultar', 'NotaFiscalController@consultar');
	Route::post('/cartaCorrecao', 'NotaFiscalController@cartaCorrecao');
	Route::get('/teste', 'NotaFiscalController@teste');
	Route::get('/consultaCadastro', 'NotaFiscalController@consultaCadastro');
	Route::post('/inutilizar', 'NotaFiscalController@inutilizar');
	Route::get('/certificado', 'NotaFiscalController@certificado');
	Route::get('/enviarXml', 'NotaFiscalController@enviarXml');


});

Route::group(['prefix' => 'cte'],function(){
	Route::get('/', 'CteController@index');
	Route::get('/nova', 'CteController@nova');
	Route::get('/lista', 'CteController@lista');
	Route::get('/detalhar/{id}', 'CteController@detalhar');
	Route::get('/delete/{id}', 'CteController@delete');
	Route::post('/salvar', 'CteController@salvar');
	Route::get('/filtro', 'CteController@filtro');
	Route::get('/custos/{id}', 'CteController@custos');
	Route::post('/saveReceita', 'CteController@saveReceita');
	Route::post('/saveDespesa', 'CteController@saveDespesa');

	Route::get('/deleteReceita/{id}', 'CteController@deleteReceita');
	Route::get('/deleteDespesa/{id}', 'CteController@deleteDespesa');

	Route::get('/consultaChave', 'EmiteCteController@consultaChave');
	Route::get('/chaveNfeDuplicada', 'CteController@chaveNfeDuplicada');
	
});

Route::group(['prefix' => 'cteSefaz'],function(){
	Route::post('/enviar', 'EmiteCteController@enviar');
	Route::get('/imprimir/{id}', 'EmiteCteController@imprimir');
	Route::get('/imprimirCCe/{id}', 'EmiteCteController@imprimirCCe');
	Route::get('/imprimirCancela/{id}', 'EmiteCteController@imprimirCancela');
	Route::post('/cancelar', 'EmiteCteController@cancelar');
	Route::post('/consultar', 'EmiteCteController@consultar');
	Route::post('/inutilizar', 'EmiteCteController@inutilizar');
	Route::post('/cartaCorrecao', 'EmiteCteController@cartaCorrecao');
	Route::get('/teste/{id}', 'EmiteCteController@teste');
	Route::get('/enviarXml', 'EmiteCteController@enviarXml');

});


Route::group(['prefix' => 'mdfe'],function(){
	Route::get('/', 'MdfeController@index');
	Route::get('/nova', 'MdfeController@nova');
	Route::get('/lista', 'MdfeController@lista');
	Route::get('/detalhar/{id}', 'MdfeController@detalhar');
	Route::get('/delete/{id}', 'MdfeController@delete');
	Route::post('/salvar', 'MdfeController@salvar');
	Route::get('/filtro', 'MdfeController@filtro');
	
});

Route::group(['prefix' => 'mdfeSefaz'],function(){
	Route::post('/enviar', 'EmiteMdfeController@enviar');
	Route::get('/imprimir/{id}', 'EmiteMdfeController@imprimir');
	Route::post('/cancelar', 'EmiteCteController@cancelar');
	Route::post('/consultar', 'EmiteCteController@consultar');
	Route::post('/inutilizar', 'EmiteCteController@inutilizar');
	Route::post('/cartaCorrecao', 'EmiteCteController@cartaCorrecao');
	Route::get('/teste/{id}', 'EmiteCteController@teste');
	Route::get('/enviarXml', 'EmiteCteController@enviarXml');

});

Route::group(['prefix' => 'nfce'],function(){
	Route::post('/gerar', 'NFCeController@gerar');
	Route::get('/imprimir/{id}', 'NFCeController@imprimir');
	Route::get('/imprimirNaoFiscal/{id}', 'NFCeController@imprimirNaoFiscal');
	Route::get('/imprimirNaoFiscalCredito/{id}', 'NFCeController@imprimirNaoFiscalCredito');
	Route::post('/cancelar', 'NFCeController@cancelar');
	Route::get('/deleteVenda/{id}', 'NFCeController@deleteVenda');

	// Route::post('/consultar', 'NotaFiscalController@consultar');
	// Route::get('/teste', 'NotaFiscalController@teste');
});

Route::group(['prefix' => 'clientes'],function(){
	Route::get('/', 'ClientController@index');
	Route::get('/delete/{id}', 'ClientController@delete');
	Route::get('/edit/{id}', 'ClientController@edit');
	Route::get('/new', 'ClientController@new');
	Route::get('/all', 'ClientController@all');
	Route::get('/verificaLimite', 'ClientController@verificaLimite');
	Route::get('/find/{id}', 'ClientController@find');
	Route::get('/pesquisa', 'ClientController@pesquisa');
	
	Route::post('/request', 'ClientController@request');
	Route::post('/save', 'ClientController@save');
	Route::post('/update', 'ClientController@update');
	Route::get('/cpfCnpjDuplicado', 'ClientController@cpfCnpjDuplicado');

});

Route::group(['prefix' => 'clientesDelivery'],function(){
	Route::get('/', 'ClienteDeliveryController@index');
	Route::get('/edit/{id}', 'ClienteDeliveryController@edit');
	Route::get('/all', 'ClienteDeliveryController@all');
	Route::post('/update', 'ClienteDeliveryController@update');


	Route::get('/pedidos/{id}', 'ClienteDeliveryController@pedidos');
	Route::get('/enderecos/{id}', 'ClienteDeliveryController@enderecos');
	Route::get('/enderecosEdit/{id}', 'ClienteDeliveryController@enderecoEdit');
	Route::get('/enderecosMap/{id}', 'ClienteDeliveryController@enderecosMap');
	Route::post('/updateEndereco', 'ClienteDeliveryController@updateEndereco');
	
	Route::get('/pesquisa', 'ClienteDeliveryController@pesquisa');
});


Route::group(['prefix' => 'transportadoras'],function(){
	Route::get('/', 'TransportadoraController@index');
	Route::get('/delete/{id}', 'TransportadoraController@delete');
	Route::get('/edit/{id}', 'TransportadoraController@edit');
	Route::get('/new', 'TransportadoraController@new');
	Route::get('/all', 'TransportadoraController@all');
	Route::get('/find/{id}', 'TransportadoraController@find');
	
	Route::post('/save', 'TransportadoraController@save');
	Route::post('/update', 'TransportadoraController@update');
});

Route::group(['prefix' => 'fornecedores'],function(){
	Route::get('/', 'ProviderController@index');
	Route::get('/delete/{id}', 'ProviderController@delete');
	Route::get('/edit/{id}', 'ProviderController@edit');
	Route::get('/new', 'ProviderController@new');
	Route::get('/all', 'ProviderController@all');
	Route::get('/find/{id}', 'ProviderController@find');

	Route::post('/request', 'ProviderController@request');
	Route::post('/save', 'ProviderController@save');
	Route::post('/update', 'ProviderController@update');
});

Route::group(['prefix' => 'compraFiscal'],function(){
	Route::get('/', 'CompraFiscalController@index');
	Route::post('/new', 'CompraFiscalController@new');
	Route::post('/salvarNfFiscal', 'CompraFiscalController@salvarNfFiscal');
	Route::post('/salvarItem', 'CompraFiscalController@salvarItem');
	Route::get('/read', 'CompraFiscalController@read');
	Route::get('/teste', 'CompraFiscalController@teste');
});

Route::group(['prefix' => 'compraManual'],function(){
	Route::get('/', 'CompraManualController@index');
	Route::post('/salvar', 'CompraManualController@salvar');
	Route::post('/salvarNfFiscal', 'CompraManualController@salvarNfFiscal');
	Route::post('/salvarItem', 'CompraManualController@salvarItem');
	Route::get('/read', 'CompraManualController@read');

	Route::get('/ultimaCompra/{produtoId}', 'CompraManualController@ultimaCompra');
});

Route::group(['prefix' => 'funcionarios'],function(){
	Route::get('/', 'FuncionarioController@index');
	Route::get('/delete/{id}', 'FuncionarioController@delete');
	Route::get('/edit/{id}', 'FuncionarioController@edit');
	Route::get('/new', 'FuncionarioController@new');
	Route::get('/all', 'FuncionarioController@all');
	Route::get('/contatos/{id}', 'FuncionarioController@contatos');
	Route::get('/editContato/{id}', 'FuncionarioController@editContato');
	Route::get('/deleteContato/{id}', 'FuncionarioController@deleteContato');
	Route::post('/saveContato', 'FuncionarioController@saveContato');
	Route::post('/updateContato', 'FuncionarioController@saveContato');

	Route::post('/request', 'FuncionarioController@request');
	Route::post('/save', 'FuncionarioController@save');
	Route::post('/update', 'FuncionarioController@update');
});

Route::group(['prefix' => 'contatoFuncionario'],function(){
	Route::get('/{funcionaId}', 'FuncionarioController@index');
	Route::get('/delete/{id}', 'FuncionarioController@delete');
	Route::get('/edit/{id}', 'FuncionarioController@edit');
	Route::get('/new/{funcionarioId}', 'FuncionarioController@new');
	Route::post('/save', 'FuncionarioController@save');
	Route::post('/update', 'FuncionarioController@update');
});

Route::group(['prefix' => 'servicos'],function(){
	Route::get('/', 'ServiceController@index');
	Route::get('/delete/{id}', 'ServiceController@delete');
	Route::get('/edit/{id}', 'ServiceController@edit');
	Route::get('/new', 'ServiceController@new');
	Route::get('/all', 'ServiceController@all');

	Route::post('/request', 'ServiceController@request');
	Route::post('/save', 'ServiceController@save');
	Route::post('/update', 'ServiceController@update');
	Route::get('/pesquisa', 'ServiceController@pesquisa');
	Route::post('/getValue', 'ServiceController@getValue');
});

Route::group(['prefix' => 'orcamento'],function(){
	Route::get('/', 'BudgetController@index');
	Route::get('/delete/{id}', 'BudgetController@delete');
	Route::get('/new', 'BudgetController@new');

	Route::get('/searchClient', 'BudgetController@searchClient');
	Route::get('/searchDate', 'BudgetController@searchDate');

	Route::get('/os/{id}', 'BudgetController@os');
	Route::post('/save', 'BudgetController@save');
});

Route::group(['prefix' => 'ordemServico'],function(){
	Route::get('/', 'OrderController@index');
	Route::get('/new', 'OrderController@new');
	Route::get('/servicosordem/{id}', 'OrderController@servicosordem');
	Route::get('/deleteServico/{id}', 'OrderController@deleteServico');
	Route::get('/addRelatorio/{id}', 'OrderController@addRelatorio');
	Route::get('/editRelatorio/{id}', 'OrderController@editRelatorio');
	Route::get('/deleteRelatorio/{id}', 'OrderController@deleteRelatorio');
	Route::get('/alterarEstado/{id}', 'OrderController@alterarEstado');
	Route::post('/alterarEstado', 'OrderController@alterarEstadoPost');
	Route::get('/filtro', 'OrderController@filtro');
	
	Route::post('/addRelatorio', 'OrderController@saveRelatorio');
	Route::post('/updateRelatorio', 'OrderController@updateRelatorio');
	Route::get('/cashFlowFilter', 'OrderController@cashFlowFilter');
	Route::post('/save', 'OrderController@save');
	Route::post('/addServico', 'OrderController@addServico');
	Route::post('/find', 'OrderController@find');

	Route::get('/print/{id}', 'OrderController@print');

	Route::get('/deleteFuncionario/{id}', 'OrderController@deleteFuncionario');
	Route::post('/saveFuncionario', 'OrderController@saveFuncionario');

	Route::get('/alterarStatusServico/{id}', 'OrderController@alterarStatusServico');
	
});

Route::group(['prefix' => 'semRegistro'],function(){
	Route::get('/', 'ApplianceNotFounController@index');
	Route::get('/delete/{id}', 'ApplianceNotFounController@delete');
});


Route::group(['prefix' => 'fluxoCaixa'],function(){
	Route::get('/', 'FluxoCaixaController@index');
	Route::get('/filtro', 'FluxoCaixaController@filtro');
	Route::get('/relatorioIndex', 'FluxoCaixaController@relatorioIndex');
	Route::get('/relatorioFiltro/{data1}/{data2}', 'FluxoCaixaController@relatorioFiltro');
});

Route::group(['prefix' => 'orcamentoCliente'],function(){
	Route::get('/', 'ClientTempController@index');
	Route::get('/delete/{id}', 'ClientTempController@delete');
});

Route::group(['prefix' => 'vendas'],function(){
	Route::get('/', 'VendaController@index');
	Route::get('/nova', 'VendaController@nova');
	Route::get('/lista', 'VendaController@lista');
	Route::get('/detalhar/{id}', 'VendaController@detalhar');
	Route::get('/delete/{id}', 'VendaController@delete');
	Route::post('/salvar', 'VendaController@salvar');
	Route::post('/salvarCrediario', 'VendaController@salvarCrediario');
	Route::get('/filtro', 'VendaController@filtro');
});

Route::group(['prefix' => 'compras'],function(){
	Route::get('/', 'PurchaseController@index');
	Route::get('/filtro', 'PurchaseController@filtro');
	Route::get('/view/{id}', 'PurchaseController@view');
	Route::get('/delete/{id}', 'PurchaseController@delete');
	Route::get('/detalhes/{id}', 'PurchaseController@detalhes');
	Route::get('/pesquisa', 'PurchaseController@pesquisa');
	Route::get('/downloadXml/{id}', 'PurchaseController@downloadXml');
	Route::post('/save', 'PurchaseController@save');
});

Route::group(['prefix' => 'estoque'],function(){
	Route::get('/', 'StockController@index');
	Route::get('/view/{id}', 'StockController@view');
	Route::get('/deleteApontamento/{id}', 'StockController@deleteApontamento');
	Route::get('/apontamentoProducao', 'StockController@apontamento');
	Route::get('/todosApontamentos', 'StockController@todosApontamentos');
	Route::get('/apontamentoManual', 'StockController@apontamentoManual');
	Route::get('/filtroApontamentos', 'StockController@filtroApontamentos');
	Route::post('/saveApontamento', 'StockController@saveApontamento');
	Route::post('/saveApontamentoManual', 'StockController@saveApontamentoManual');

});

Route::group(['prefix' => 'cotacao'],function(){
	Route::get('/', 'CotacaoController@index');
	Route::get('/new', 'CotacaoController@new');
	Route::post('/salvar', 'CotacaoController@salvar');
	
	Route::get('/deleteItem/{id}', 'CotacaoController@deleteItem');
	Route::get('/delete/{id}', 'CotacaoController@delete');
	Route::get('/edit/{id}', 'CotacaoController@edit');
	Route::get('/alterarStatus/{id}/{status}', 'CotacaoController@alterarStatus');
	Route::post('/saveItem', 'CotacaoController@saveItem');

	Route::get('/view/{id}', 'CotacaoController@view');
	Route::get('/clonar/{id}', 'CotacaoController@clonar');
	Route::post('/clonarSave', 'CotacaoController@clonarSave');


	Route::get('/response/{code}', 'CotacaoController@response');


	Route::get('/searchProvider', 'CotacaoController@searchProvider');
	Route::get('/searchPiece', 'CotacaoController@searchPiece');


	Route::get('/sendMail/{id}', 'CotacaoController@sendMail');
	
});

Route::get('/response/{code}', 'CotacaoResponseController@response');
Route::post('/responseSave', 'CotacaoResponseController@responseSave');


Route::group(['prefix' => 'frenteCaixa'],function(){
	Route::get('/', 'FrontBoxController@index');
	Route::get('/list', 'FrontBoxController@list');
	Route::get('/devolucao', 'FrontBoxController@devolucao');
	Route::get('/filtro', 'FrontBoxController@filtro');

	Route::get('/filtroCliente', 'FrontBoxController@filtroCliente');
	Route::get('/filtroNFCe', 'FrontBoxController@filtroNFCe');
	Route::get('/filtroValor', 'FrontBoxController@filtroValor');
	Route::get('/deleteVenda/{id}', 'FrontBoxController@deleteVenda');
});


Route::get('/ola', function() {
	return view('default/ola')->with('title', 'Bem vindo ao teste do SlymPDV');
});

Route::group(['prefix' => 'clienteDelivery'],function(){
	Route::get('/all', 'AppUserController@all');

});

Route::group(['prefix' => 'push'],function(){
	Route::get('/', 'PushController@index');
	Route::get('/new', 'PushController@new');
	Route::post('/save', 'PushController@save');
	Route::post('/update', 'PushController@update');

	Route::get('/send/{id}', 'PushController@send');
	Route::get('/edit/{id}', 'PushController@edit');
	Route::get('/delete/{id}', 'PushController@delete');

});

Route::group(['prefix' => 'codigoDesconto'],function(){
	Route::get('/', 'CodigoDescontoController@index');
	Route::get('/new', 'CodigoDescontoController@new');
	Route::post('/save', 'CodigoDescontoController@save');
	Route::post('/update', 'CodigoDescontoController@update');
	Route::get('/edit/{id}', 'CodigoDescontoController@edit');

	Route::get('/delete/{id}', 'CodigoDescontoController@delete');
	Route::get('/push/{id}', 'CodigoDescontoController@push');
	Route::post('/push', 'CodigoDescontoController@savePush');
	Route::get('/sms/{id}', 'CodigoDescontoController@sms');
	Route::post('/sms', 'CodigoDescontoController@saveSms');
	Route::get('/alterarStatus/{id}', 'CodigoDescontoController@alterarStatus');
});

Route::group(['prefix' => 'tamanhosPizza'],function(){
	Route::get('/', 'TamanhoPizzaController@index');
	Route::get('/new', 'TamanhoPizzaController@new');
	Route::post('/save', 'TamanhoPizzaController@save');
	Route::post('/update', 'TamanhoPizzaController@update');
	Route::get('/edit/{id}', 'TamanhoPizzaController@edit');

	Route::get('/delete/{id}', 'TamanhoPizzaController@delete');

});

Route::group(['prefix' => 'categoriaDespesa'],function(){
	Route::get('/', 'CategoriaDespesaController@index');
	Route::get('/new', 'CategoriaDespesaController@new');
	Route::post('/save', 'CategoriaDespesaController@save');
	Route::post('/update', 'CategoriaDespesaController@update');
	Route::get('/edit/{id}', 'CategoriaDespesaController@edit');

	Route::get('/delete/{id}', 'CategoriaDespesaController@delete');

});

Route::group(['prefix' => 'veiculos'],function(){
	Route::get('/', 'VeiculoController@index');
	Route::get('/new', 'VeiculoController@new');
	Route::post('/save', 'VeiculoController@save');
	Route::post('/update', 'VeiculoController@update');
	Route::get('/edit/{id}', 'VeiculoController@edit');
	Route::get('/delete/{id}', 'VeiculoController@delete');
});

Route::group(['prefix' => 'devolucao'],function(){
	Route::get('/', 'DevolucaoController@index');
	Route::get('/nova', 'DevolucaoController@new');
	Route::post('/new', 'DevolucaoController@renderizarXml');
	Route::post('/salvar', 'DevolucaoController@salvar');
	Route::post('/enviarSefaz', 'DevolucaoController@enviarSefaz');
	Route::get('/ver/{id}', 'DevolucaoController@ver');
	Route::get('/delete/{id}', 'DevolucaoController@delete');
	Route::get('/imprimir/{id}', 'DevolucaoController@imprimir');
	Route::get('/downloadXmlEntrada/{id}', 'DevolucaoController@downloadXmlEntrada');
	Route::get('/downloadXmlDevolucao/{id}', 'DevolucaoController@downloadXmlDevolucao');
});


