function enviar(){
	let id = 0
	let cont = 0;
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++;
		}
	})

	if(cont > 1){
		alert('Por favor selecione apenas um item da tabela!')
	}else{
		if(cont > 0){
			$('#preloader1').css('display', 'block');

			let token = $('#_token').val();

			$.ajax
			({
				type: 'POST',
				data: {
					devolucao_id: id,
					_token: token
				},
				url: path + 'devolucao/enviarSefaz',
				dataType: 'json',
				success: function(e){

					console.log(e)
					let recibo = e;
					let retorno = recibo.substring(0,4);
					console.log(retorno)
					let mensagem = recibo.substring(5,recibo.length);
					if(retorno == 'Erro'){
						let m = JSON.parse(mensagem);

						$('#modal-alert-erro').modal('open');
						$('#evento-erro').html("[" + m.protNFe.infProt.cStat + "] : " + m.protNFe.infProt.xMotivo)

					}
					else if(e == 'false'){
						alert("Esta NF já esta aprovada, não é possível enviar novamente!");
					}
					else{
						$('#modal-alert').modal('open');
						$('#evento').html("Devolução emitida com sucesso RECIBO: "+recibo)
						window.open(path+"/devolucao/imprimir/"+id, "_blank");
					}

					$('#preloader1').css('display', 'none');
				}, error: function(e){
					Materialize.toast('Erro de comunicação contate o desenvolvedor', 5000)
					console.log(e)
					$('#preloader1').css('display', 'none');
				}
			});
		}else{
			alert('Selecione um documento para envio!')
		}
	}
}

function imprimir(){
	let id = 0;
	let cont = 0;
	$('#body tr').each(function(){
		if($(this).find('#checkbox input').is(':checked')){
			id = $(this).find('#id').html();
			cont++;
		}
	})
	if(cont > 1){
		alert('Selecione apenas um documento da lista!');
	}else{
		if(id > 0){
			window.open(path+"/devolucao/imprimir/"+id, "_blank");
		}else{
			Materialize.toast('Selecione um documento para imprimir', 4000)

		}
	}

}

