
function enviar(id){
	alert(id)
	var r = confirm("Deseja gerar Entrada fiscal desta Compra?");
	if (r == true) {
		

		let token = $('#_token').val();
		$.ajax
		({
			type: 'POST',
			data: {
				compra_id: id,
				_token: token
			},
			url: path + 'compras/gerarEntrada',
			dataType: 'json',
			success: function(e){
				console.log(e)
				// let recibo = e;
				// let retorno = recibo.substring(0,4);
				// let mensagem = recibo.substring(5,recibo.length);
				// if(retorno == 'Erro'){
				// 	let m = JSON.parse(mensagem);

				// 	$('#modal-alert-erro').modal('open');
				// 	$('#evento-erro').html("[" + m.protNFe.infProt.cStat + "] : " + m.protNFe.infProt.xMotivo)

				// }
				// else if(e == 'Apro'){
				// 	alert("Esta NF já esta aprovada, não é possível enviar novamente!");
				// }
				// else{
				// 	$('#modal-alert').modal('open');
				// 	$('#evento').html("NF-e gerada com sucesso RECIBO: "+recibo)
				// 	window.open(path+"/nf/imprimir/"+id, "_blank");
				// }

				// $('#preloader1').css('display', 'none');
			}, error: function(e){

				let js = e.responseJSON;
				console.log(js)
				// if(js.message){
				// 	Materialize.toast(js.message, 5000)
				// }else{
				// 	let err = "";
				// 	js.map((v) => {
				// 		err += v + "\n";
				// 	});
				// 	alert(err);
				// }

				// $('#preloader1').css('display', 'none');
			}
		});
	}else{

	}

}