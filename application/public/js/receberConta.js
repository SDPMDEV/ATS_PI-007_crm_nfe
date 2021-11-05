var DIFERENCA = 0
var CONTAS = [];
var SOMA = 0;
var VALORPADRAO = 0
$(function () {

  DIFERENCA = $('#diferenca').val()
  VALORPADRAO = parseFloat($('#valor_padrao').val())

  $('#contas').val('');
});

function adicionarConta(id, valor){

  if(CONTAS.includes(id)){
    $('#div_'+id).css('background', '#fff')
    remover(id, (call) => {

    })
    SOMA -= parseFloat(valor)
  }else{
    verificaSoma(valor, (verifica) => {
      if(verifica){
        CONTAS.push(id)
        $('#div_'+id).css('background', 'lightgreen')
        SOMA += parseFloat(valor)
      }
    })

  }

  $('#contas').val(CONTAS)
  $('#somatorio').html((SOMA + VALORPADRAO).toFixed(2))

}

function verificaSoma(valor, call){
  let v = true;
  valor = parseFloat(valor)
  if((SOMA + valor) > DIFERENCA){
    swal('Saldo insuficiente', 'Erro ao unir pagamentos', 'error')
    v = false
  }
  call(v)
}

function remover(id, call){
  let temp = [];
  CONTAS.map((c) => {
    if(c != id) temp.push(c)
  })
  CONTAS = temp;
  call(true)
}