
$(function(){
	$('.btn-act-template').attr('href',base_url+"settings/m_index_pendapatan/template");
	$('.btn-act-export').attr('href',base_url+"settings/m_index_pendapatan/export");
	$('#index_kali').addClass('money2');
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
function formOpen(){
	money_init();
}
