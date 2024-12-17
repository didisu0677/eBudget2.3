
var xhr_ajax = null;
$(document).ready(function(){
	getData();
	loadData2()
});
$('#filter_cabang').on('change',function(){
	getData();
	loadData2();
});
function getData(){
	var kode_cabang = $('#filter_cabang option:selected').val();
	var cabang = $('#filter_cabang').val();
	if(!cabang){ return ''; }
	var page = base_url + 'transaction/data_kantor_budget_planner/get_data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();
		
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

    $('#form-command .error').html('');
    $('#form-command .is-invalid').removeClass('is-invalid');

	if(cabang){
		cLoader.open(lang.memuat_data + '...');
		xhr_ajax = $.ajax({
			url 	: page,
			data 	: {},
			type	: 'get',
			dataType: 'json',
			success	: function(response) {
				xhr_ajax = null;
				if(!response.status){
					cLoader.close();
					cAlert.open(response.message,'failed');
					$('#form-command input').val('');
					return false;
				}
				if(response){
					v = response;
					$('#id').val(v.id);
					$('#kode_cabang').val(v.kode_cabang);
					$('#kode_cabang').val(v.kode_cabang);
					$('#nama_kantor').val(v.nama_kantor);
					$('#nama_pimpinan').val(v.nama_pimpinan);
					$('#no_hp_pimpinan').val(v.no_hp_pimpinan);
					$('#tgl_mulai_menjabat').val(v.tgl_mulai_menjabat);
					$('#nama_cp').val(v.nama_cp);
					$('#nama_cp2').val(v.nama_cp2);
					$('#no_hp_cp').val(v.no_hp_cp);
					$('#no_hp_cp2').val(v.no_hp_cp2);
					$('#email_Cp').val(v.email_Cp);
					$('#email_lainnya').val(v.email_lainnya);

					if(response.access_edit){
						$('#form-command input').prop('disabled',false);
						$('#form-command button').prop('disabled',false);
						$('#form-command button').show();
					}else{
						$('#form-command input').prop('disabled',true);
						$('#form-command button').prop('disabled',true);
						$('#form-command button').hide();
					}

				}else{
					$('#kode_cabang').val(kode_cabang);
				}
				cLoader.close();
				cek_autocode();
			}
		});
	}
}

var xhr_ajax2 = null;
function loadData2(){

    if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }

    var cabang = $('#filter_cabang').val();
    if(!cabang){ return ''; }
    var page = base_url + 'transaction/data_kantor_budget_planner/data2/';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	if(cabang){
  		xhr_ajax2 = $.ajax({
	        url: page,
	        type: 'post',
			data : $('#form-filter').serialize(),
	        dataType: 'json',
	        success: function(res){
	        	xhr_ajax2 = null;
	        	if(!res.status){
	        		$('#result2 tbody').html('');
	        		return false;	
	        	}
	            $('#result2 tbody').html(res.data);				
	        }
	    });
  	}
}

$('#create-berita-acara').click(function(e){
	e.preventDefault();
	$('#modal-berita-acara').modal();
});
$('.btn-wa').on('click',function(){
	var id = $(this).attr('data-id');
	if(id){
		var val = $('#'+id).val();
		if(validatePhone(val)){
			var hashids = new Hashids(encode_key);
    		var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
			var post_data = {
		        "csrf_token"    	: x[0],
		        "phone" 			: val,
		    }
		    var url = base_url + 'api/redirect_wa';
		    $.redirect(url,post_data,"","_blank");
		}
	}
})
function validatePhone(txt) {
    var filter = /^[0-9-+]+$/;
    if (filter.test(txt)) {
        return true;
    }
    else {
    	cAlert.open("invalid phone number",'info');
        return false;
    }
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
    var dt_table = get_data_table('#result2');
    var arr_data = dt_table['arr'];
    var arr_header = dt_table['arr_header'];

    var post_data = {
        "header" 			: JSON.stringify(arr_header),
        "data"        		: JSON.stringify(arr_data),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/data_kantor_budget_planner/export';
    $.redirect(url,post_data,"","_blank");
});
function get_data_table(classnya){
    var arr = [];
    var arr_header = [];
    var no = 0;
    var index_cabang = 0;
    $(classnya+" table tr").each(function() {
        var arrayOfThisRowHeader = [];
        var tableDataHeader = $(this).find('th');
        if (tableDataHeader.length > 0) {
            tableDataHeader.each(function(k,v) {
                var val = $(this).text();
                arrayOfThisRowHeader.push($(this).text());
            });
            arr_header.push(arrayOfThisRowHeader);
        }

        var arrayOfThisRow = [];
        var tableData = $(this).find('td');
        if (tableData.length > 0) {
            tableData.each(function() {
                var val = $(this).text();
                if($(this).hasClass('sb-1')){
                    val = '     '+$(this).text();
                }else if($(this).hasClass('sb-2')){
                    val = '          '+$(this).text();
                }else if($(this).hasClass('sb-3')){
                    val = '               '+$(this).text();
                }else if($(this).hasClass('sb-4')){
                    val = '                    '+$(this).text();
                }else if($(this).hasClass('sb-5')){
                    val = '                         '+$(this).text();
                }else if($(this).hasClass('sb-6')){
                    val = '                              '+$(this).text();
                }
                arrayOfThisRow.push(val); 
            });
            arr.push(arrayOfThisRow);
        }
    });
    return {'arr' : arr, 'arr_header' : arr_header};
}
