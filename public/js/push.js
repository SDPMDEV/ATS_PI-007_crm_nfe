
$(function () {

	getClientes(function(data){
	    $('input.autocomplete-cliente').autocomplete({
	      data: data,
	      limit: 20, 
	      onAutocomplete: function(val) {
	        var cliente = $('#autocomplete-cliente').val().split('|');

	      },
	      minLength: 1,
	    });
	});


});

function getClientes(data){
  $.ajax
  ({
    type: 'GET',
    url: path + 'clienteDelivery/all',
    dataType: 'json',
      success: function(e){
       // console.log(e);
        data(e)

      }, error: function(e){
        console.log(e)
      }

  });
}

$('#todos').change(() => {
  let t = $('#todos').is(':checked');
  $('#autocomplete-cliente').val('')
  if(t){
    $('#cliente').css('display', 'none');
  }else{
    $('#cliente').css('display', 'block');

  }
})
