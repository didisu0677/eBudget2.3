
	$('.btn-import').click(function(){
		$('#form-import')[0].reset();

	    $('#modal-import .alert').hide();
	    $('#modal-import').modal('show');

	    var val = $('#currency option').eq(0).val();
	    if(val){
	    	$('#currency').val(val).trigger('change');
	    }
	});
	
    $(document).on('click','.btn-template',function(){
		console.log('masul');
		var a = 'https://ebudget2.aplikasinusa.com/assets/templateExcel/templateRekapTarget.xlsx';
		window.open(a);
	});
	$(document).ready(function(){
		get_currency();
	});
	function get_currency(){
		$.ajax({
			url : base_url + 'api/currency_option',
			type : 'post',
			data : {},
			dataType : 'json',
			success : function(response) {
				$('#currency').html(response.data);
			}
		});
	}
