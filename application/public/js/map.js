
var pedidosPendentes = []
var pedidosFinalizados = []
$(document).ready(function(){
	let uri = window.location.pathname;
	$('#select-filtro').val('pendentes').change()
	if(uri.split('/')[1] == 'pedidosDelivery'){

		pedidosPendentes = JSON.parse($('#pedidosPendentes').val())
		pedidosFinalizados = JSON.parse($('#pedidosFinalizados').val())
		
		montaEntregasPendentes()
		
	}else{
		getVars((res) => {
			console.log(res)
			if(res){
				initMap(res);
			}
		})
	}
})

function getVars(call){
	let latLocal = $('#lat_local').val();
	let lngLocal = $('#lng_local').val();
	let latCliente = $('#lat_cliente').val();
	let lngCliente = $('#lng_cliente').val();
	let js = {
		latLocal: parseFloat(latLocal),
		lngLocal: parseFloat(lngLocal),
		latCliente: parseFloat(latCliente),
		lngCliente: parseFloat(lngCliente)
	}
	call(js);
}

function initMap(positions){
	var myLatLng = {lat: positions.latLocal, lng: positions.lngLocal};
	var cliLatLng = {lat: positions.latCliente, lng: positions.lngCliente};

	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: 14,
		center: myLatLng
	});

	console.log(myLatLng.lat)
	var directionsService = new google.maps.DirectionsService();
	var directionsRequest = {
		origin: new google.maps.LatLng(myLatLng.lat, myLatLng.lng),
		destination: new google.maps.LatLng(cliLatLng.lat, cliLatLng.lng),

		travelMode: google.maps.DirectionsTravelMode.DRIVING,
		unitSystem: google.maps.UnitSystem.METRIC
	};

	directionsService.route(
		directionsRequest,
		function(response, status)
		{	
			console.log(response)
			let route = response.routes[0].legs[0];
			let distancia = route.distance.text;
			let duracao = route.duration.text;

			$('#distancia').html(distancia)
			$('#duracao').html(duracao)

			if (status == google.maps.DirectionsStatus.OK)
			{
				new google.maps.DirectionsRenderer({
					map: map,
					directions: response
				});
			}
			else
				$("#error").append("Unable to retrieve your route<br />");
		});

}

function montaEntregasPendentes(){
	let latLocal = parseFloat($('#lat_local').val());
	let lngLocal = parseFloat($('#lng_local').val());
	var myLatLng = {lat: latLocal, lng: lngLocal};

	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: 14,
		center: myLatLng
	});

	pedidosPendentes.map((p) => {
		console.log(p)
		var myLatLng = {lat: parseFloat(p.endereco.latitude), lng: parseFloat(p.endereco.longitude)};
		new google.maps.Marker({
			position: myLatLng,
			map,
			title: p.cliente.nome + " R$ " + p.valor_total,
			icon: {
				url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
			}
		});

	})
}

function montaEntregasFinalizados(){
	let latLocal = parseFloat($('#lat_local').val());
	let lngLocal = parseFloat($('#lng_local').val());
	var myLatLng = {lat: latLocal, lng: lngLocal};

	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: 14,
		center: myLatLng
	});

	pedidosFinalizados.map((p) => {
		console.log(p)
		var myLatLng = {lat: parseFloat(p.endereco.latitude), lng: parseFloat(p.endereco.longitude)};
		new google.maps.Marker({
			position: myLatLng,
			map,
			title: p.cliente.nome + " R$ " + p.valor_total,
			icon: {
				url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
			}
		});

	})
}

function montaEntregasAmbos(){
	let latLocal = parseFloat($('#lat_local').val());
	let lngLocal = parseFloat($('#lng_local').val());
	var myLatLng = {lat: latLocal, lng: lngLocal};

	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: 14,
		center: myLatLng
	});

	pedidosPendentes.map((p) => {
		console.log(p)
		var myLatLng = {lat: parseFloat(p.endereco.latitude), lng: parseFloat(p.endereco.longitude)};
		new google.maps.Marker({
			position: myLatLng,
			map,
			title: p.cliente.nome + " R$ " + p.valor_total,
			icon: {
				url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
			}
		});

	})
	pedidosFinalizados.map((p) => {
		console.log(p)
		var myLatLng = {lat: parseFloat(p.endereco.latitude), lng: parseFloat(p.endereco.longitude)};
		new google.maps.Marker({
			position: myLatLng,
			map,
			title: p.cliente.nome + " R$ " + p.valor_total,
			icon: {
				url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
			}
		});

	})
}

$('#select-filtro').change(() => {
	let filtro = $('#select-filtro').val()
	if(filtro == 'pendentes'){
		montaEntregasPendentes()
	}else if(filtro == 'finalizados'){
		montaEntregasFinalizados()
	}else{
		montaEntregasAmbos();
	}
})