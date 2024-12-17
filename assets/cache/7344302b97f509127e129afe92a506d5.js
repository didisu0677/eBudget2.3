
var xhr_ajax = null;
var controller = 'status_to_budget_nett_labarugi';
$(function(){
	loadData();
})

function loadData(){
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;

        	if(!res.status){
        		cLoader.close();
        		$('.d-content .card-body').html('');
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$('.d-content .card-body').html(res.view);
        	$('.d-content .card-header').html(res.title);
    		resize_window();
    		checkSubData();
    		cLoader.close();
		}
    });
}
