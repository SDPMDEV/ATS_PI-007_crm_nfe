
$(function () {
  let cidadeId = $('#cidadeId').val();
  getCidades(function(data){
   $('input.autocomplete-cidade').autocomplete({
     data: data,
     limit: 20, 
     onAutocomplete: function(val) {
       var cliente = $('#autocomplete-cidade').val().split('|');

     },
     minLength: 1,
   });
 });

  

  if(cidadeId > 0){

    findCidade(cidadeId, (data) => {

      $('#autocomplete-cidade').val(data.id + ' - ' + data.nome)
    })
  }


});

function getCidades(data){

  $.ajax
  ({
    type: 'GET',
    url: path + 'cidades/all',
    dataType: 'json',
    success: function(e){
       // console.log(e);
       data(e)

     }, error: function(e){
      console.log(e)
    },
    error: function(err){
      alert('Ocorreu um erro ao buscar as cidades, revise o arquivo .env PATH_URL')
    }

  });
}

function findCidade(cidadeId, data){
  $.ajax
  ({
    type: 'GET',
    url: path + 'cidades/find/'+cidadeId,
    dataType: 'json',
    success: function(e){
      data(e)
    }, error: function(e){
      console.log(e)
    }

  });
}
