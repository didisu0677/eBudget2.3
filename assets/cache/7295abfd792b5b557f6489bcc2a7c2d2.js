
var dt_coa 			  = '';
var dt_index = 0;
var response_data = [];
var controller = 'plan_biaya_umum';
var status_group = 0;
$(document).ready(function () {
	var page_data = $('.page-data').data();
	if(page_data && page_data.status_group == 1){
		status_group = 1;
	}
	if(status_group == 1){
		$('.l-cabang').hide();
		$('#filter_cabang').next(".select2-container").hide();
	}
	getData();
	resize_window();
});
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').on('change',function(){
	if(status_group == 0){
		getData();
	}
});
$('#filter_cabang_induk').change(function(){
	if(status_group == 1){
		getData();
	}
});
function getData() {
	var cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
	}
	if(!cabang){ return ''; }
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+cabang;

	$.ajax({
		url 	: page,
		data 	: { status_group : status_group },
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			response_data = [];
			if(!response.status){
				cLoader.close();
				$('.table-app tbody').html('');
				cAlert.open(response.message,'failed');
				return false;
			}
			$('.table-app tbody').html(response.table);
			dt_coa = response.coa;
			cLoader.close();
			var item_act	= {};
			if($('.table-app tbody .btn-input').length > 0) {
				item_act['edit'] 		= {name : lang.ubah, icon : "edit"};
			}

			var kode_cabang;
			var cabang ;

			kode_cabang = $('#user_cabang').val();
			cabang = $('#filter_cabang').val();

			if(!response.access_edit) {	
				$(".btn-add").prop("disabled", true);
				$(".btn-input").prop("disabled", true);
				$(".btn-save").prop("disabled", true);	
			}else{
				$(".btn-add").prop("disabled", false);
				$(".btn-input").prop("disabled", false);
				$(".btn-save").prop("disabled", false);	
			}
			
			var act_count = 0;
			for (var c in item_act) {
				act_count = act_count + 1;
			}
			if(act_count > 0) {
				$.contextMenu({
			        selector: '.table-app tbody tr', 
			        callback: function(key, options) {
			        	if($(this).find('[data-key="'+key+'"]').length > 0) {
				        	if(typeof $(this).find('[data-key="'+key+'"]').attr('href') != 'undefined') {
				        		window.location = $(this).find('[data-key="'+key+'"]').attr('href');
				        	} else {
					        	$(this).find('[data-key="'+key+'"]').trigger('click');
					        }
					    } 
			        },
			        items: item_act
			    });
			}
		}
	});
}

$(document).on('dblclick','.table-app tbody td .badge',function(){
	if($(this).closest('tr').find('.btn-input').length == 1) {
		var badge_status 	= '0';
		var data_id 		= $(this).closest('tr').find('.btn-input').attr('data-id');
		if( $(this).hasClass('badge-danger') ) {
			badge_status = '1';
		}
		active_inactive(data_id,badge_status);
	}
});

// edit value
$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
	var val = $(this).text();
	var minus = val.includes("(");
	if(minus){
		val = val.replace('(','');
		val = val.replace(')','');
		$(this).text('-'+val);
	}
});
$(document).on('blur','.edit-value',function(){
	var tr = $(this).closest('tr');
	var angka = $(this).text();
	angka = moneyToNumber(angka);
	if(angka != $(this).attr('data-value')) {
		$(this).addClass('edited');
	}else{
		$(this).removeClass('edited');
	}
	if(tr.find('td.edited').length > 0) {
		tr.addClass('edited-row');
	} else {
		tr.removeClass('edited-row');
	}
	var val = $(this).text();
	var minus = val.includes("-");
	if(minus){
		val = val.replace('-','');
		$(this).text('('+val+')');
	}
});
$(document).on('keyup','.edit-value',function(e){
	var n = $(this).text();
	n = formatCurrency(n,'',2);
    $(this).text(n.toLocaleString());
    var selection = window.getSelection();
	var range = document.createRange();
	selection.removeAllRanges();
	range.selectNodeContents($(this)[0]);
	range.collapse(false);
	selection.addRange(range);
	$(this)[0].focus();
});
function formatCurrency(angka, prefix,decimal){
	min_txt     = angka.split("-");
    str_min_txt = '';
	var number_string = angka.replace(/[^,\d]/g, '').toString(),
	split   		= number_string.split(','),
	sisa     		= split[0].length % 3,
	rupiah     		= split[0].substr(0, sisa),
	ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

	// tambahkan titik jika yang di input sudah menjadi angka ribuan
	if(ribuan){
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}
	if(split[1] != undefined && split[1].toString().length > decimal){
		split[1] = split[1].substr(0,decimal);
	}
	if(min_txt.length == 2){
      str_min_txt = "-";
    }
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	// return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
	return str_min_txt+rupiah;
}

function calculate() {
	var total_budget = 0;

	$('#result tbody tr').each(function(){
		if($(this).find('.budget').length == 1) {
			var subtotal_budget = moneyToNumber($(this).find('.budget').val());
			total_budget += subtotal_budget;
		}


	});

	$('#total_budget').val(total_budget);
}

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
	var cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
	}
	var anggaran = $('#filter_anggaran').val();	
	$.ajax({
		url : base_url + 'transaction/'+controller+'/save_perubahan',
		data 	: {
			'json' : jsonString,
			kode_cabang : cabang,
			kode_anggaran : anggaran,
			status_group : status_group,
			verifikasi : i
		},
		type : 'post',
		success : function(response) {
			if(response.status == 'failed'){
				cAlert.open(response.message,'failed');
				return false;
			}
			cAlert.open(response.message,'success','refreshData');
		}
	})
}

// keterangan
$(document).on('click','.btn-keterangan',function(){
	var title = $(this).attr('data-title');
	get_keterangan(title);
})
var xhr_keterangan = null;
function get_keterangan(title){
	var cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
	}
	var data_post = {
	    	kode_cabang : cabang,
	    	kode_anggaran : $('#filter_anggaran option:selected').val(),
	    	status_group : status_group,
    	}

    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/keterangan_view';
    xhr_keterangan = $.ajax({
        url: page,
        type: 'post',
		data : data_post,
        dataType: 'json',
        success: function(res){
        	xhr_keterangan = null;
        	if(!res.status){
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$('#modal-keterangan').modal('show');
			$('#modal-keterangan .modal-title').text(title);

			$('#modal-keterangan #kode_cabang').val(res.kode_cabang);
			$('#modal-keterangan #kode_anggaran').val(data_post.kode_anggaran);
			$('#modal-keterangan #id').val(res.id);
			window.CKEDITOR.instances.keterangan.setData(res.keterangan);

			if(res.access_edit){
       			$('#modal-keterangan #keterangan').removeAttr('data-readonly');
       			$('#modal-keterangan #keterangan').closest('.form-group').show();
       			$('#modal-keterangan .d-keterangan').empty();
       			$('#modal-keterangan button[type="submit"]').show();
       		}else{
       			$('#modal-keterangan #keterangan').attr('data-readonly',"true");
       			if(res.keterangan.length<=0){
       				res.keterangan = lang.data_tidak_ditemukan;
       			}
       			$('#modal-keterangan .d-keterangan').html(res.keterangan);
       			$('#modal-keterangan #keterangan').closest('.form-group').hide();
       			$('#modal-keterangan button[type="submit"]').hide();
       		}
			cLoader.close();
        }
    });
}
function refreshKeterangan(){
	$('.btn-keterangan').click()
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();

    var classnya = ['#result1'];
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

    	var cabang = $('#filter_cabang_induk option:selected').val();
    	var cabang_txt = $('#filter_cabang_induk option:selected').text();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
		var cabang_txt = $('#filter_cabang option:selected').text();
	}

    var post_data = {
        "data"        		: JSON.stringify(dt),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: cabang,
        "kode_cabang_txt"   : cabang_txt,
        "status_group" : status_group,
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

                if($(this).find('select').length>0){
                	val = $(this).find('select option:selected').text();
                }

                arrayOfThisRow.push(val); 
            });
            arr.push(arrayOfThisRow);
        }
    });
    return {'arr' : arr, 'arr_header' : arr_header};
}
