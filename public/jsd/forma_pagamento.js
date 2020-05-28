
let enderecoSelecionado = null;
var TOTAL = 0;
var VALORENTREGA = 0;
var ENTREGA = false;
var DESCONTO = 0;
var DESCONTOAPLICADO = false;
var latPadrao = 0;
var lngPadrao = 0;

let lat = latPadrao;
let lng = lngPadrao;

$(function(){

	lat = latPadrao = $('#lat_padrao').val()
	lng = lngPadrao = $('#lng_padrao').val()

	TOTAL = $('#total-init').val();
	TOTAL = parseFloat(TOTAL);
	$('#total').html("R$ "+TOTAL.toFixed(2))

	let cupom = $('#cupom').val();
	if(cupom.length == 6){
		calculaCupom(cupom);
	}else{
		if(cupom.length > 1){
			$('#cupom-invalido').css('display', 'block');
			$('#desconto').css('display', 'none');
		}
	}

	getCurrentLocation((crd) => {

		if(crd){
			initMap(crd.latitude, crd.longitude);
		}else{
			alert('Não foi possivel recuperar sua localização, ative e recarregue a pagina!');
			initMap(latPadrao, lngPadrao);
		}
	});

})

$('#cupom').on('keyup', () => {
	let cupom = $('#cupom').val();
	if(cupom.length == 6){
		calculaCupom(cupom);

	}else{
		DESCONTOAPLICADO = false;
		TOTAL += DESCONTO;
		DESCONTO = 0;
		if(cupom.length > 3){
			$('#total').html("R$ "+parseFloat(TOTAL).toFixed(2))
			$('#cupom-invalido').css('display', 'block');
			$('#desconto').css('display', 'none');
		}else{
			$('#cupom-invalido').css('display', 'none');
		}
	}
})

function calculaCupom(cupom){

	getCupom(cupom, (res) => {
		let js = JSON.parse(res)
		console.log(js)
		if(js){
			$('#cupom-invalido').css('display', 'none');
			$('#desconto').css('display', 'block');
			$('#valor-cupom').html((js.tipo == 'valor' ? 'R$': '') + parseFloat(js.valor).toFixed(2) 
				+ (js.tipo == 'percentual' ? '%': ''))
			if(js.tipo == 'valor'){
				DESCONTO = parseFloat(js.valor);
				if(!DESCONTOAPLICADO) TOTAL = TOTAL - DESCONTO;
				DESCONTOAPLICADO = true;
			}else{
				let v = parseFloat(js.valor);
				DESCONTO = ((TOTAL*v)/100);
				if(!DESCONTOAPLICADO) TOTAL = TOTAL - DESCONTO;
				DESCONTOAPLICADO = true;
			}
			console.log(TOTAL)
			$('#total').html("R$ "+parseFloat(TOTAL).toFixed(2))
		}
	})
}

$('#maquineta').click(() => {
	$('#div_do_troco').css('display', 'none');
})
$('#pagseguro').click(() => {
	$('#div_do_troco').css('display', 'none');
	$('#modal-pagseguro').html()
})
$('#dinheiro').click(() => {
	$('#div_do_troco').css('display', 'block');
})

function getCupom(cupom, call){
	$.get(path+'carrinho/cupom/'+cupom)
	.done((sucess) => {
		call(sucess)
	})
	.fail((err) => {
		call(err)
	})
}

$('#salvar_endereco').click(() => {
	let rua = $('#rua').val()
	let numero = $('#numero').val()
	let bairro = $('#bairro').val()
	let referencia = $('#referencia').val()
	let cliente_id = $('#cliente_id').val()
	let tk = $('#_token').val()

	let js = {
		rua: rua,
		numero: numero,
		bairro: bairro,
		referencia: referencia,
		cliente_id: cliente_id,
		latitude: lat,
		longitude: lng
	}

	console.log(js)

	$.post(path+'enderecoDelivery/save', {_token : tk, data: js})
	.done(function(data){
		data = JSON.parse(data)
		console.log(data)
		let ht = '<div class="col-lg-4 col-md-6" onclick="set_endereco('+data.id+')">'+
		'<div id="endereco_select_'+data.id+'" class="card border-0 med-blog">'+

		'<div class="card-body border border-top-0">'+
		'<h5 class="blog-title card-title m-0">'+
		rua + ', ' + numero+
		'</h5>'+
		'<h5>'+bairro+'</h5>'+
		'<p>Referencia: '+ referencia +'</p>'+
		'</div>'+
		'</div>'+
		'</div>';

		$( ".ends" ).append( ht );
	})
	.fail( function(err) {
		console.log(err)

	});

})

function set_endereco(id){

	$('#endereco_select_'+enderecoSelecionado).css('background', '#fff')
	if(id == 'balcao'){
		$('#endereco_select_balcao').css('background', '#81c784')
		$('#acrescimo-entrega').css('display', 'none')
		if(ENTREGA == true){
			TOTAL -= parseFloat(VALORENTREGA)
			$('#total').html("R$ "+parseFloat(TOTAL).toFixed(2))
		}
		ENTREGA = false;

	}else{
		getValorEntrega((d) => {
			let j = JSON.parse(d)
			if(ENTREGA == false){
				VALORENTREGA = j.valor_entrega;
				TOTAL += parseFloat(VALORENTREGA)
				$('#acrescimo-entrega').css('display', 'block')
				$('#valor-entrega').html(VALORENTREGA)
				$('#total').html("R$ "+parseFloat(TOTAL).toFixed(2))
			}
			ENTREGA = true;
		})
		$('#endereco_select_balcao').css('background', '#fff')

		$('#endereco_select_'+id).css('background', '#81c784')
		
	}
	enderecoSelecionado = id;
}


$('#finalizar-venda').click(() => {
	let formaPagamento = $('#debito').is(':checked') ? 'debito' :  
	$('#credito').is(':checked') ? 'credito' : $('#dinheiro').is(':checked') ?
	'dinheiro' : '';
// formaPagamento = $('#credito').is(':checked') ? 'credito' : '';
// formaPagamento = $('#dinheiro').is(':checked') ? 'dinheiro' : '';
let troco = $('#troco_para').val();
let telefone = $('#telefone').val();
let cupom = $('#cupom').val();
let js = {
	forma_pagamento: formaPagamento,
	troco: troco.replace(",", "."),
	observacao: $('#observacao').val(),
	endereco_id: enderecoSelecionado,
	pedido_id: $('#pedido_id').val(),
	telefone: telefone,
	desconto: DESCONTO,
	cupom: DESCONTO > 0 ? cupom : ''
}
console.log(js)
if(!formaPagamento){
	alert("Por favor selecione a forma de pagamento")
}
else if(!enderecoSelecionado){
	alert("Por favor selecione a forma de entrega")

}
else if(telefone.length == 0){
	alert("Por favor informe um telefone de contato")
}

else if(formaPagamento == 'dinheiro' && troco.length == 0 || parseFloat(troco.replace(",", ".")) == 0){
	alert("Por favor insira o valor de troco para")
}

else if(formaPagamento == 'dinheiro' && parseFloat(troco.replace(",", ".")) < TOTAL){
	alert("Valor do troco deve ser maior que o valor total do pedido")
	if(ENTREGA) alert('Total com entrega: R$' + TOTAL.toFixed(2))
		else alert('Total: R$' + TOTAL.toFixed(2))
	}

else{
	console.log(js)
	let tk = $('#_token').val()

	$.post(path+'carrinho/finalizarPedido', {_token : tk, data: js})
	.done(function(data){
		data = JSON.parse(data)
		console.log(data)

		sucesso(data.id)

	})
	.fail( function(err) {
		console.log(err)

	});

}

})

function getValorEntrega(call){
	$.get(path+'carrinho/configDelivery')
	.done(function(data){
		console.log(data)
		call(data)
	})
	.fail(function(err){
		console.log(err)
		call(err)
	})
}

function sucesso(id){
	$('#content').css('display', 'none');
	$('#anime').css('display', 'block');
	setTimeout(() => {
		location.href = path + 'carrinho/finalizado/'+id;
	}, 4000)
}


function getCurrentLocation(call){
	var options = {
		enableHighAccuracy: true,
		timeout: 5000,
		maximumAge: 0
	};

	function success(pos) {
		var crd = pos.coords;
		call(crd);
	};

	function error(err) {
		console.warn('ERROR(' + err.code + '): ' + err.message);
		call(false)
	};

	navigator.geolocation.getCurrentPosition(success, error, options);
}


function initMap(lat, lng){
	lat = lat;
	lng = lng;
	const position = new google.maps.LatLng(lat, lng);

	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: 16,
		center: position,
		disableDefaultUI: false
	});	

	console.log(lat)

	const marker = new google.maps.Marker({
		position: position,
		map: map,
		animation: google.maps.Animation.BOUNCE,
		draggable: true
	})

	getEnderecoByCoords(lat, lng, (res) => {

		if(res == false){

		}else{
			$('#rua').val(res.rua)
			$('#numero').val(res.numero)
			validaCamposNovoEndereco();
		}
	})

	google.maps.event.addListener(marker, 'dragend', (event) => {
		var myLatLng = event.latLng;
		var lat = myLatLng.lat();
		var lng = myLatLng.lng();

		lat = lat;
		lng = lng;
		console.log(lat)

		getEnderecoByCoords(lat, lng, (res) => {
			if(res == false){

			}else{
				$('#rua').val(res.rua)
				$('#numero').val(res.numero)
				validaCamposNovoEndereco();
			}
		})
	})

}

function getEnderecoByCoords(lat, lng, call){
	geocoder = new google.maps.Geocoder();
	var latlng = new google.maps.LatLng(lat, lng);
	geocoder.geocode({
		'latLng': latlng
	}, function (results, status) {
		if (status === google.maps.GeocoderStatus.OK) {
			if (results[1]) {
				let res = results[1].address_components;
				let numero = res[0].long_name;
				let rua = res[1].long_name;

				let js = {
					numero: res[0].long_name,
					rua: res[1].long_name
				};

				call(js);
				
			} else {
				console.log('No results found');
				call(false);
			}
		} else {
			console.log('Geocoder failed due to: ' + status);
			call(false);
		}
	});
}

$('#btn-end-map').click(() => {
	$('#info-mapa').css('display', 'none');
	$('#form-endereco').css('display', 'block');
})

$('#novo-endereco').click(() => {
	$('#info-mapa').css('display', 'block');
	$('#form-endereco').css('display', 'none');
})

$('.fr').keyup(() => {
	validaCamposNovoEndereco()
})

function validaCamposNovoEndereco(){
	let rua = $('#rua').val();
	let numero = $('#numero').val();
	let bairro = $('#bairro').val();
	let referencia = $('#referencia').val();

	if(rua.length > 0 && numero.length > 0 && 
		bairro.length > 0 && referencia.length){
		$('#salvar_endereco').removeClass('disabled')
}else{
	$('#salvar_endereco').addClass('disabled')
}
}

$('#abrir-mapa').click(() => {
	$('#info-mapa').css('display', 'block');
	$('#form-endereco').css('display', 'none');
})
