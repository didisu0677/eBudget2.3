
$('#coa').on('change',function(){
	var txt = $(this).find('option:selected').text();
	var arr = txt.split(' - ');
	if(arr.length>1){
		txt = arr[1];
	}
	$('#nama').val(txt);
})
$('#id_struktur_cabang').on('change',function(){
	var txt = $(this).find('option:selected').text();
	$('#struktur_cabang').val(txt);
})
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
$('.btn-act-import').click(function(){
    var val = $('#currency option').eq(0).val();
    if(val){
    	$('#currency').val(val).trigger('change');
    }
});
