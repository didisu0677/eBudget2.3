
var status_group = 0;
var controller = 'plan_rekap_asumsi';
$(document).ready(function(){
	resize_window();
	var page_data = $('.page-data').data();
	if(page_data && page_data.status_group == 1){
		status_group = 1;
	}
	if(status_group == 1){
		$('.l-cabang').hide();
		$('#filter_cabang').next(".select2-container").hide();
	}
	getData();
})
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){
	if(status_group == 0){
		getData();
	}
});
$('#filter_cabang_induk').change(function(){
	if(status_group == 1){
		getData();
	}
});
var xhr_ajax = null;
function getData(){
	var cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
	}
	if(!cabang){ return ''; }
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ cabang
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : { status_group : status_group },
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	if(!res.status){
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$('.table-app tbody').html(res.view);
        	cLoader.close();
        	checkSubData();
		}
    });
}
$(document).on('click','.btn-export',function(){
	var cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
	}
	if(!cabang){ return ''; }

	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var post_data = {
        "csrf_token"    	: x[0],
        "export" 			: "export",
        "status_group" 		: status_group,
    }
    var url = base_url + 'transaction/'+controller+'/data';
    url += '/'+ $('#filter_anggaran').val();
    url += '/'+ cabang
    $.redirect(url,post_data,"","_blank");
});
