
function enviar(id){
	var r = confirm("Deseja gerar entrada fiscal desta Compra?");
	if (r == true) {
		
		$('#preloader').css('display', 'block')
		let token = $('#_token').val();
		$.ajax
		({
			type: 'POST',
			data: {
				compra_id: id,
				natureza: $('#natureza').val(),
				tipo_pagamento: $('#tipo_pagamento').val(),
				_token: token
			},
			url: path + 'compras/gerarEntrada',
			dataType: 'json',
			success: function(e){
				$('#preloader').css('display', 'none')

				console.log(e)

				$('#modal-alert').modal('open');
				$('#evento').html("NF-e de Entrada emitida com sucesso RECIBO: "+e)
				window.open(path+"compras/imprimir/"+id, "_blank");

			}, error: function(e){
				$('#preloader').css('display', 'none')

				console.log(e)
				let js = e.responseJSON;
				console.log(js)
			}
		});
	}else{

	}

}

function redireciona(){
	location.reload();
}