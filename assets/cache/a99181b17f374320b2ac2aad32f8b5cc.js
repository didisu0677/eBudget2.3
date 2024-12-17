
$(function(){
	$.ajax({
		url 	: base_url+'api/coa_option',
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			$('#coa').html(response.data);
		}
	});
})
$('#coa').on('change',function(){
	var val = $(this).find('option:selected').text();
	val = val.split(' - ');
	$('#nama').val(val[1]);
})
