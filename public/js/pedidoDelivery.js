$('#btn-enviar-push').click(() => {

	let titulo = $('#titulo-push').val();
	let texto = $('#texto-push').val();
	let imagem = $('#imagem-push').val();

	let js = {
		titulo: titulo,
		texto: texto,
		imagem: imagem,
		cliente: $('#cliente').val(),
		_token: $('#token').val()
	}
	console.log(path)
	$.post(path+'pedidosDelivery/sendPush', js)
	.done((success) => {
		console.log(success)
		alert('Push Enviado')
		$('#modal-push').modal('close')

	})
	.fail((err) => {
		console.log(err)
		alert('Erro ao enviar Push')
	})
})