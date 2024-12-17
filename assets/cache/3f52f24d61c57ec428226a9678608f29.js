
$(document).ready(function(){
	resize_window();
	loadData();

});	

$('#filter_anggaran').change(function(){
	loadData();
});

$('#filter_cabang').change(function(){
	loadData();
});
var xhr_ajax = null;
function loadData(){
	var cabang = $('#filter_cabang').val();
	if(!cabang) return '';
	$('.table-1 tbody').html('');	
	$('.table-2 tbody').html('');	
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/formula_kredit/data/';
        page += '/'+ $('#filter_anggaran').val();
    	page += '/'+ $('#filter_cabang').val();
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
            $('.table-1 tbody').html(res.table);
            $('.table-2 tbody').html(res.table2);
            if(res.access_edit){
                $('.btn-save').prop('disabled',false);
                $('.btn-save').show();
            }else{
                $('.btn-save').prop('disabled',true);
                $('.btn-save').hide();
            }
            cLoader.close();
            persent_init();
		}
    });
}

$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
	if(!$(this).hasClass('percent')){
		var val = $(this).text();
		var minus = val.includes("(");
		if(minus){
			val = val.replace('(','');
			val = val.replace(')','');
			$(this).text('-'+val);
		} 
	}
	
});
$(document).on('blur','.edit-value',function(){
	var tr = $(this).closest('tr');
	if($(this).text() != $(this).attr('data-value')) {
		$(this).addClass('edited');
	}
	if(tr.find('td.edited').length > 0) {
		tr.addClass('edited-row');
	} else {
		tr.removeClass('edited-row');
	}
	if(!$(this).hasClass('percent')){
		var val = $(this).text();
		var minus = val.includes("-");
		if(minus){
			val = val.replace('-','');
			$(this).text('('+val+')');
		}
	}
});
$(document).on('keyup','.edit-value',function(e){
	if(!$(this).hasClass('percent')){
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
	}
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
		console.log(split[1].toString().length);
		split[1] = split[1].substr(0,decimal);
	}
	if(min_txt.length == 2){
      str_min_txt = "-";
    }
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	// return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
	return str_min_txt+rupiah;
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
	var page = base_url + 'transaction/formula_kredit/save_perubahan';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
	$.ajax({
		url : page,
		data 	: {
			'json' : jsonString,
			verifikasi : i
		},
		type : 'post',
		success : function(response) {
			cAlert.open(response,'success','loadData');
		}
	})
}
$(document).on('click','.btn-remove',function(){
	var dt_id = $(this).attr('data-id');
	if(dt_id){
		del_id = dt_id;
		urlDelete 	= base_url+"transaction/formula_kredit/delete";
		urlDelete += '/'+$('#filter_anggaran option:selected').val();
		urlDelete += '/'+$('#filter_cabang option:selected').val();
		cConfirm.open(lang.anda_yakin_menghapus_data_ini + '?','deleteData');
	}
})
