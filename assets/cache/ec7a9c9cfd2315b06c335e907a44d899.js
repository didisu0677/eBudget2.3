
$(function(){
	loadCoa();
})
function loadCoa(){
	cLoader.open(lang.memuat_data + '...');
	$.ajax({
		url 	: base_url + 'api/coa_option',
		data 	: {},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			$('#coa').html(response.data);
			cLoader.close();
		}
	});
}
$('#coa').on('change',function(){
	var name = $('#coa option:selected').text();
	name = name.split(' - ');
	if(name.length>1){
		name = name[1]
	}else{
		name = '';
	}
	$('#nama').val(name)
})
