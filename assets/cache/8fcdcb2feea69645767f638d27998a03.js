
var controller = 'usulan_besaran';
$(function(){
	getData();
})
$('#filter_tahun').change(function(){
	getData();
});

$('#filter_cabang').change(function(){
	getData();
});

var xhr_ajax = null;
function getData(){
	var cabang = $('#filter_cabang').val();
	if(!cabang){
		return '';
	}

	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

    $('.div-content').html();
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();
	xhr_ajax = $.ajax({
		url 	: page,
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			if(!response.status){
				cLoader.close();
				cAlert.open(response.message,'failed');
				return false;
			}
			$('.div-content').html(response.view);
			cLoader.close();
			money_init();
			if(response.access_edit){
				$('.btn-save').show();
			}else{
				$('.btn-save').hide();
			}

			$(document).find('.dt_child').hide();
			$.each(response.coa_show,function(k,v){
				$(document).find('.dt_'+v).show();
			})
		}
	});
}
$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
});
$(document).on('blur','.edit-value',function(){
	var tr = $(this).closest('tr');
	if($(this).text() != $(this).attr('data-value')) {
		$(this).addClass('edited');
	}else{
		$(this).removeClass('edited');
	}
	if(tr.find('td.edited').length > 0) {
		tr.addClass('edited-row');
	} else {
		tr.removeClass('edited-row');
	}
});
$(document).on('click','.btn-save',function(){
	var i = 0;
	$('.edited').each(function(){
		i++;
	});
	if(i == 0) {
		cAlert.open('tidak ada data yang di ubah');
	} else {
		var msg 	= lang.anda_yakin_menyetujui;
		if( i == 0) msg = lang.anda_yakin_menolak;
		cConfirm.open(msg,'save_perubahan');        
	}

});
function save_perubahan() {
	var data_edit = {};
	var i = 0;
	
	$('.edited').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);
	var page = base_url + 'transaction/'+controller+'/save_perubahan';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();
	$.ajax({
		url : page,
		data 	: {
			'json' : jsonString,
			verifikasi : i,
		},
		type : 'post',
		success : function(response) {
			if(response.status == 'failed'){
				cAlert.open(response.message,'failed');
				return false;
			}
			cAlert.open(response.message,'success','loadData');
		}
	})
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
    var dt_table = get_data_table('.div-content');
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
    var url = base_url + 'transaction/usulan_besaran/export';
    $.redirect(url,post_data,"","_blank");
});
function get_data_table(classnya){
	var arr = [];
    var arr_header = [];
    var no = 0;
    var index_cabang = 0;
    var count = $(classnya).find('table').length;
    for (var i = 0; i < count; i++) {
    	var title = $(classnya).find('.card-header').eq(i).text();
    	var arrayOfThisRow = [];
    	arrayOfThisRow.push('');
    	arrayOfThisRow.push(title);
    	for (var ii = 1; ii <= 12; ii++) {
    		arrayOfThisRow.push('-');
    	}
    	arr.push(arrayOfThisRow);

    	$(classnya).find('table').eq(i).find('tr').each(function() {
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
	    var arrayOfThisRow = [];
    	arrayOfThisRow.push('');
    	arrayOfThisRow.push('');
    	for (var ii = 1; ii <= 12; ii++) {
    		arrayOfThisRow.push('-');
    	}
    	arr.push(arrayOfThisRow);
    }
    return {'arr' : arr, 'arr_header' : arr_header};
}
