
var controller = 'e_kartu_biaya_divisi';
var dt_index = 0;
var xhr_ajax = null;
var delete_callback = loadData;
var first = true;
$(document).ready(function(){
	var coa_selected = $('.page-data').attr('data-coa_selected');
	if(coa_selected){
		$('#filter_coa').val(coa_selected).trigger('change');
	}
	loadData();
});
$('#filter_anggaran').change(function(){
	loadData();
});
$('#filter_cabang').change(function(){
	loadData();
});
$('#filter_coa').change(function(){
	var val = $(this).val();
	if(val){
		var url = base_url+'transaction/'+controller+'/option_sub_coa';
		cLoader.open(lang.memuat_data + '...');
		$.ajax({
	        url: url,
	        type: 'post',
	        data : {
	        	coa : val,
	        	kode_anggaran : $('#filter_anggaran option:selected').val(),
	        },
	        dataType: 'json',
	        success: function(res){
	      		if(!res.status){
	      			cLoader.close();
	      			$('#filter_sub_coa').html('');
	      			cAlert.open(res.message,'failed');
	      		}
	      		$('#filter_sub_coa').html(res.data);
	      		$('#filter_sub_coa').val(res.selected).trigger('change');
	        	cLoader.close();
			}
	    });
	}
});
$('#filter_sub_coa').change(function(){
	var val = $(this).val();
	if(val){
		first = false;
		loadData();
	}
});
var status_gab = false;
function loadData(){
	$('#modal-form').modal('hide');
	if(first){
		return false;
	}
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){
		return false;
	}

	var filter_coa = $('#filter_coa option:selected').val();
	if(!filter_coa){
		cAlert.open('coa not found','failed');
		return false;
	}

	var filter_sub_coa = $('#filter_sub_coa option:selected').val();
	if(!filter_sub_coa){
		cAlert.open('sub coa not found','failed');
		return false;
	}

    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
        data: {
        	'kode_anggaran' : $('#filter_anggaran option:selected').val(),
        	'kode_cabang' 	: cabang,
        	'coa' 			: filter_coa,
        	'sub_coa' 		: filter_sub_coa,
        },
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	if(!res.status){
        		$('#result1 tbody').html('');
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$.each(res.class,function(k,v){
        		$(k).html(v);
        	});
        	status_gab = res.status_gab;
        	if(status_gab){
        		$('.d-gab').removeClass('d-none');
        		$('.d-gab').show(300);
        	}else{
        		$('.d-gab').hide(300);
        	}
        	resize_window();
        	cLoader.close();
		}
    });
}
var is_edit = false;
function formOpen(){
	var cabang 		= $('#filter_cabang option:selected').val();
	var cabang_txt 	= $('#filter_cabang option:selected').text();
	$('#kode_cabang').html('<option value="'+cabang+'">'+cabang_txt+'</option>');
	$('#kode_cabang').val(cabang).trigger('change');

	var coa 	= $('#filter_coa option:selected');
	var sub_coa = $('#filter_sub_coa option:selected');

	$('#coa').val(coa.val());
	$('#coa_txt').val(coa.text());

	$('#sub_coa').val(sub_coa.val());
	$('#sub_coa_txt').val(sub_coa.text());

	$('#d-item tbody').empty();
	var response = response_edit;
	is_edit = false;
	if(typeof response.id != 'undefined') {
		is_edit = true;
		if(response.status == 'failed'){
			cAlert.open(response.message,'failed');
			return false;
		}
		add_item();
		$('#id').val(response.id);
		$('#d-item .keterangan').last().val(response.keterangan);
		$('#d-item .tanggal').last().val(response.tanggal).trigger('change');
		$('#d-item .biaya').last().val(response.biaya).trigger('change');
		$('#d-item .dt_id').last().val(response.id).trigger('change');
	}else{
		add_item();
	}

	if(is_edit){
		$('.btn-add-item').hide();
	}else{
		$('.btn-add-item').show();
	}
}
$('.btn-add-item').click(function(){
	add_item();
});
$(document).on('click','.btn-remove',function(){
	$(this).closest('tr').remove();
});
function add_item(){
	dt_index += 1;
	var date = $('#date').val();
	var item = `<tr>`;
	item += `<td><input type="hidden" class="dt_id" name="dt_id[]"/><input type="hidden" class="dt_key" value="`+dt_index+`" name="dt_key[]"/><input type="text" autocomplete="off" class="form-control text-right tanggal w-100-per" name="tanggal[]" aria-label="" data-validation="required" data-readonly="true" readonly value="`+date+`"/></td>`;
	item += `<td><input type="text" autocomplete="off" class="form-control keterangan w-100-per" name="keterangan[]" aria-label="" data-validation="required"/></td>`;
	item += `<td><input type="text" autocomplete="off" class="form-control text-right biaya money w-100-per" name="biaya[]" aria-label="" data-validation="required"/></td>`;
	item += `<td><button type="button"class="btn btn-sm btn-icon-only btn-danger btn-remove"><i class="fa-times"></i></button></td>`;
	item += `</tr>`;

	$('#d-item tbody').append(item);
	money_init();
	// datepicker_init();
	var $t = $('#d-item .pilihan:last-child');
	$.each($t,function(k,o){
		var $o = $(o);
		$o.select2({
			dropdownParent : $o.parent(),
			placeholder : ''
		});
	})

	if(is_edit){
		$('.btn-remove').hide();
	}else{
		$('.btn-remove').show();
	}
}

function delete_callback(){
	loadData();
}
// keyup
$(document).on('keyup', function(e) {
  if (e.key == "Escape"){
  	if($('.filter-panel').hasClass('active')){
  		$('.filter-close').click();	
  	}else{
  		$('.btn-filter-panel-show').click();
  	}
  }else if (event.keyCode == 13) {     
		if(event.shiftKey){
			$('.btn-search').click();
		}
  }
});
$('.btn-search').on('click',function(){
	loadData();
})
$('.btn-cancel').on('click',function(){
	$('.filter-close').click();
})
var status_id;
$(document).on('click','.btn-active',function(){
	status_id = $(this).attr('data-id');
	if(!status_id){
		cAlert.open('data not found','failed');
		return false;
	}
	cConfirm.open(lang.anda_yakin_mengubah_data_ini+' ?','save_status');
})
function save_status(){
	$.ajax({
		url : base_url + 'transaction/'+controller+'/save_status',
		data 	: {
			'id' 			: status_id,
			'kode_cabang' 	: $('#filter_cabang option:selected').val(),
			'kode_anggaran' : $('#filter_anggaran option:selected').val(),
			'coa' 			: $('#filter_coa option:selected').val(),
			'sub_coa' 		: $('#filter_sub_coa option:selected').val(),
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

    var classnya = ['#result1','#result2','#result3','.d-detail'];
    var dt = {};
    $.each(classnya,function(k,v){
    	var dt_table = get_data_table(v);
    	var arr_data = dt_table['arr']
    	var arr_header = dt_table['arr_header'];
    	dt[v] = {
    		header : arr_header,
    		data : arr_data
    	}
    });

    var post_data = {
        "data"        		: JSON.stringify(dt),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "status_gab" 		: status_gab,
        "nama_gab" 			: $('#gab-cab').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/'+controller+'/export';
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

// file
$(document).on('click','.btn-file',function(){
	var id = $(this).attr('data-id');
	getFileView(id);
})
function refreshFile(){
	var id = $('#modal-file #id_monitoring_anggaran').val();
	getFileView(id);
}
var xhr_file_view = null;
function getFileView(id){
	if( xhr_file_view != null ) {
        xhr_file_view.abort();
        xhr_file_view = null;
    }

    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/file_view';

    var data_post = {
    	id_monitoring_anggaran : id,
    	kode_cabang : $('#filter_cabang option:selected').val(),
    	kode_anggaran : $('#filter_anggaran option:selected').val(),
    	coa : $('#filter_coa option:selected').val(),
    	sub_coa : $('#filter_sub_coa option:selected').val(),
    	title : $('#filter_sub_coa option:selected').text(),
    }

  	xhr_file_view = $.ajax({
        url: page,
        type: 'post',
		data : data_post,
        dataType: 'json',
        success: function(res){
        	xhr_file_view = null;
       		
       		$('#modal-file').modal('show');
       		$('#modal-file .modal-title').text('File '+res.title);
       		$('#modal-file .modal-body').attr('data-title',res.title);

       		$('#additional-file').html('');
       		$('#modal-file #id_monitoring_anggaran').val(data_post.id_monitoring_anggaran);
       		$('#modal-file #kode_cabang').val(data_post.kode_cabang);
       		$('#modal-file #kode_anggaran').val(data_post.kode_anggaran);
       		$('#modal-file #coa').val(data_post.coa);
       		$('#modal-file #sub_coa').val(data_post.sub_coa);
       		$('#modal-file #d-none').text('');

       		var length = 0;
       		if(res.list && typeof res.list.id != 'undefined') {
       			$('#modal-file #id').val(res.list.id);
       			length = res.list.file.length;
       			$.each(res.list.file,function(n,z){
       				var key = n.split('--');
       				if(key.length>1){
       					n = key[1];
       				}
       				var btn_remove = '';
       				var v_readonly = ' data-readonly="true"';
       				if(res.access_edit){
       					btn_remove = '<button type="button" class="btn btn-danger btn-remove-file btn-block btn-icon-only"><i class="fa-times"></i></button>';
       					v_readonly = '';
       				}
					var konten = '<div class="form-group row">'
						+ '<div class="col-sm-3 col-4 offset-sm-3">'
						+ '<input type="text" class="form-control" autocomplete="off" value="'+n+'" name="keterangan_file[]" '+v_readonly+' placeholder="'+lang.keterangan+'" data-validation="required" aria-label="'+lang.keterangan+'">'
						+ '</div>'
						+ '<div class="col-sm-4 col-5">'
						+ '<input type="hidden" class="form-control" name="file[]" autocomplete="off" value="exist:'+z+'">'
						+ '<div class="input-group">'
						+ '<input type="text" class="form-control" autocomplete="off" disabled value="'+z+'">'
						+ '<div class="input-group-append">'
						+ '<a href="'+base_url+'assets/uploads/'+controller+'/'+z+'" target="_blank" class="btn btn-info btn-icon-only"><i class="fa-download"></i></a>'
						+ '</div>'
						+ '</div>'
						+ '</div>'
						+ '<div class="col-sm-1 col-3">'
						+ btn_remove
						+ '</div>'
						+ '</div>';
					$('#additional-file').append(konten);
				});
       		}else{
       			$('#modal-file #id').val('');
       		}

       		if(res.access_edit){
       			$('.d-file').show();
       			$('#modal-file button[type="submit"]').show();
       		}else{
       			$('.d-file').hide();
       			$('#modal-file button[type="submit"]').hide();
       			if(length<=0){
       				var item = `<div class="form-group row d-file">
						<label class="col-form-label col-sm-9">Data Tidak Ditemukan</label>
					</div>`;
					$('#modal-file #d-none').append(item);
       			}
       		}

            cLoader.close();
		}
    });
}
$('#add-file').click(function(){
	$('#upl-file').click();
});
var accept 	= Base64.decode(upl_alw);
var regex 	= "(\.|\/)("+accept+")$";
var re 		= accept == '*' ? '*' : new RegExp(regex,"i");
$('#upl-file').fileupload({
	maxFileSize: upl_flsz,
	autoUpload: false,
	dataType: 'text',
	acceptFileTypes: re
}).on('fileuploadadd', function(e, data) {
	$('#add-file').attr('disabled',true);
	data.process();
	is_autocomplete = true;
}).on('fileuploadprocessalways', function (e, data) {
	if (data.files.error) {
		var explode = accept.split('|');
		var acc 	= '';
		$.each(explode,function(i){
			if(i == 0) {
				acc += '*.' + explode[i];
			} else if (i == explode.length - 1) {
				acc += ', ' + lang.atau + ' *.' + explode[i];
			} else {
				acc += ', *.' + explode[i];
			}
		});
		cAlert.open(lang.file_yang_diizinkan + ' ' + acc + '. ' + lang.ukuran_file_maks + ' : ' + (upl_flsz / 1024 / 1024) + 'MB');
		$('#add-file').text($('#add-file').attr('title')).removeAttr('disabled');
	} else {
		data.submit();
	}
	is_autocomplete = false;
}).on('fileuploadprogressall', function (e, data) {
	var progress = parseInt(data.loaded / data.total * 100, 10);
	$('#add-file').text(progress + '%');
}).on('fileuploaddone', function (e, data) {
	if(data.result == 'invalid' || data.result == '') {
		cAlert.open(lang.gagal_menunggah_file,'error');
	} else {
		var spl_result = data.result.split('/');
		if(spl_result.length == 1) spl_result = data.result.split('\\');
		if(spl_result.length > 1) {
			var spl_last_str = spl_result[spl_result.length - 1].split('.');
			if(spl_last_str.length == 2) {
				var filename = data.result;
				var f = filename.split('/');
				var fl = filename.split('temp');
				var fl_link = base_url + 'assets/uploads/temp' + fl[1];
				var konten = '<div class="form-group row">'
							+ '<div class="col-sm-3 col-4 offset-sm-3">'
							+ '<input type="text" class="form-control" autocomplete="off" value="" name="keterangan_file[]" placeholder="'+lang.keterangan+'" data-validation="required" aria-label="'+lang.keterangan+'">'
							+ '</div>'
							+ '<div class="col-sm-4 col-5">'
							+ '<input type="hidden" class="form-control" name="file[]" autocomplete="off" value="'+data.result+'">'
							+ '<div class="input-group">'
							+ '<input type="text" class="form-control" autocomplete="off" disabled value="'+f[f.length - 1]+'">'
							+ '<div class="input-group-append">'
							+ '<a href="'+fl_link+'" target="_blank" class="btn btn-info btn-icon-only"><i class="fa-download"></i></a>'
							+ '</div>'
							+ '</div>'
							+ '</div>'
							+ '<div class="col-sm-1 col-3">'
							+ '<button type="button" class="btn btn-danger btn-remove-file btn-block btn-icon-only"><i class="fa-times"></i></button>'
							+ '</div>'
							+ '</div>';
				$('#additional-file').append(konten);
			} else {
				cAlert.open(lang.file_gagal_diunggah,'error');
			}
		} else {
			cAlert.open(lang.file_gagal_diunggah,'error');						
		}
	}
	$('#add-file').text($('#add-file').attr('title')).removeAttr('disabled');
	is_autocomplete = false;
}).on('fileuploadfail', function (e, data) {
	cAlert.open(lang.gagal_menunggah_file,'error');
	$('#add-file').text($('#add-file').attr('title')).removeAttr('disabled');
	is_autocomplete = false;
}).on('fileuploadalways', function() {

});
$(document).on('click','.btn-remove-file',function(){
	$(this).closest('.form-group').remove();
});
