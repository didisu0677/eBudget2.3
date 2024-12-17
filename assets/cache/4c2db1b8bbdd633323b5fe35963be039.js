
var xhr_ajax = null;
$('.btn-refresh').on('click',function(){
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoading.open();
	$.ajax({
		url 	: base_url+'settings/rate/check_cabang',
		type	: 'post',
		data 	: {
			kode_anggaran : $('#filter_anggaran option:selected').val(),
		},
		dataType: 'json',
		success	: function(response) {
			cLoading.close();
			cAlert.open(response.message,response.status,response.load);
		}
	});
})
