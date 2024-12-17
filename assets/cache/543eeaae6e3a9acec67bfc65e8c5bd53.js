
var controller = 'm_data_core_rekap_rasio_view';
var xhr_ajax = null;
$(function(){
	resize_window();
})
$(document).on('click','.btn-refresh',function(){
    getData();
});
function getData(){
	var cabang = $('#filter_cabang').val();
	if(!cabang){
		return '';
	}
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'settings/'+controller+'/data';
    page += '/'+ $('#filter_tahun').val();
    page += '/'+ cabang;
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : {},
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	$('#result1 tbody').html(res.table);
        	cLoader.close();
		}
    });
}
