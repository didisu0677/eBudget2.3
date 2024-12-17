
var menu_selected = 0;
$(function(){
	menu_finish();
	resize_window();
})
var xhr_ajax = null;
function menu_finish(){
	$('#modal-form').modal('hide');
	var data_post = {
		kode_anggaran 	: $('#filter_anggaran option:selected').val(),
	};

	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/finish/menu';
    xhr_ajax = $.ajax({
		url 	: page,
		data 	: data_post,
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			$('#menu').html(response.data);
			$('#menu').val(menu_selected).trigger('change');
			cLoader.close();
		}
	});
}
$('#menu').on('change',function(){
	menu_selected = $(this).val();
})
$(document).on('click','.btn-input',function(){
	$('#modal-form form')[0].reset();
	$('#modal-form .modal-footer').addClass('hidden').html('');
	$('#modal-form [name="id"]').val(0);
	$('#modal-form [name="kode_anggaran"]').val($('#filter_anggaran option:selected').val());
	$('#modal-form .tab-pane').removeClass('show').removeClass('active');
	$('#modal-form .tab-pane').first().addClass('show').addClass('active');
	$('.nav-tabs .nav-link').removeClass('active');
	$('.nav-tabs .nav-link').first().addClass('active');
	$('.chk-all').prop('indeterminate',false).prop('checked',false);
	$('#modal-form form .is-invalid').each(function(){
		$(this).removeClass('is-invalid');
		$(this).closest('.form-group').find('.error').remove();
	});
	$('#info-tab').click();
	if($(this).data('id') == 0) {
		$('#modal-form .modal-title').html('Tambah');
		$('#modal-form [type="submit"]').text('Simpan');
		$('#modal-form').modal();
	}else{
		$('#modal-form .modal-title').html('Edit');
		$('#modal-form [type="submit"]').text('Update');
		var getUrl = '';
		if(typeof $('#modal-form form').attr('data-edit') != 'undefined') {
			getUrl = $('#modal-form form').attr('data-edit');
		} else {
			var curUrl = $('#modal-form form').attr('action');
			var parseUrl = curUrl.split('/');
			var lastPath = parseUrl[parseUrl.length - 1];
			if(lastPath == '') lastPath = parseUrl[parseUrl.length - 2];
			getUrl = curUrl.replace(lastPath,'get_data');
		}
		$.ajax({
			url			: getUrl,
			data 		: {'id':$(this).attr('data-id')},
			type		: 'post',
			cache		: false,
			dataType	: 'json',
			success		: function(response) {
				if(typeof response['status'] == 'undefined' && typeof response['message'] == 'undefined') {
					$('#modal-form [name="id"]').val(response.id);
					$('#modal-form [name="nama"]').val(response.nama);
					$('#modal-form [name="keterangan"]').val(response.keterangan);
					if(response.is_active == '0') {
						$('#modal-form [name="is_active"]').prop('checked',false);
					}
					$.each(response.access,function(k,v){
						$('#modal-form').find('#act_view-'+v.id_menu).prop('checked',true);
					});
					$('#modal-form').modal();
					$('#modal-form .modal-footer').html('');
					var footer_text = '';
					var create_info = '';
					var update_info = '';
					if(typeof response['create_by'] != 'undefined' && typeof response['create_at'] != 'undefined') {
						if(response['create_at'] != '0000-00-00 00:00:00') {
							var create_by = response['create_by'] == '' ? 'Unknown' : response['create_by'];
							var create_at = response['create_at'].split(' ');
							var tanggal_c = create_at[0].split('-');
							var waktu_c = create_at[1].split(':');
							var date_c = tanggal_c[2]+'/'+tanggal_c[1]+'/'+tanggal_c[0]+' '+waktu_c[0]+':'+waktu_c[1];
							create_info += '<small>Dibuat oleh <strong>' + create_by + ' </strong> @ ' + date_c + '</small>';
						}
					}
					if(typeof response['update_by'] != 'undefined' && typeof response['update_at'] != 'undefined') {
						if(response['update_at'] != '0000-00-00 00:00:00') {
							var update_by = response['update_by'] == '' ? 'Unknown' : response['update_by'];
							var update_at = response['update_at'].split(' ');
							var tanggal_u = update_at[0].split('-');
							var waktu_u = update_at[1].split(':');
							var date_u = tanggal_u[2]+'/'+tanggal_u[1]+'/'+tanggal_u[0]+' '+waktu_u[0]+':'+waktu_u[1];
							update_info += '<small>Diupdate oleh <strong>' + update_by + ' </strong> @ ' + date_u + '</small>';
						}
					}
					if(create_info || update_info) {
						footer_text += '<div class="w-100">';
						footer_text += create_info;
						footer_text += update_info;
						footer_text += '</div>';
					}
					if(footer_text) {
						$('#modal-form .modal-footer').html(footer_text).removeClass('hidden');
					}
				} else {
					cAlert.open(response['message'],response['status']);
				}
			}
		});
	}
});
$('.chk-child').click(function(){
	if($(this).is(':checked')) {
		var c0 = $(this).attr('data-parent');
		var c1 = $('#' + c0).attr('data-parent');
		var c2 = $('#' + c1).attr('data-parent');
		var c3 = $('#' + c2).attr('data-parent');
		$('#' + c0).prop('checked',true);
		$('#' + c1).prop('checked',true);
		$('#' + c2).prop('checked',true);
		$('#' + c3).prop('checked',true);
		if($('#' + c0).closest('.form-group').find('.chk-child:enabled:checked').length == $('#' + c0).closest('.form-group').find('.chk-child:enabled').length) {
			$('#' + c0).closest('.form-group').find('.chk-all').prop('checked',true); 
		} else {
			$('#' + c0).closest('.form-group').find('.chk-all').prop('indeterminate',true); 
		}
		if($('#' + c1).closest('.form-group').find('.chk-child:enabled:checked').length == $('#' + c1).closest('.form-group').find('.chk-child:enabled').length) {
			$('#' + c1).closest('.form-group').find('.chk-all').prop('checked',true); 
		} else {
			$('#' + c1).closest('.form-group').find('.chk-all').prop('indeterminate',true); 
		}
		if($('#' + c2).closest('.form-group').find('.chk-child:enabled:checked').length == $('#' + c2).closest('.form-group').find('.chk-child:enabled').length) {
			$('#' + c2).closest('.form-group').find('.chk-all').prop('checked',true); 
		} else {
			$('#' + c2).closest('.form-group').find('.chk-all').prop('indeterminate',true); 
		}
		if($('#' + c3).closest('.form-group').find('.chk-child:enabled:checked').length == $('#' + c3).closest('.form-group').find('.chk-child:enabled').length) {
			$('#' + c3).closest('.form-group').find('.chk-all').prop('checked',true); 
		} else {
			$('#' + c3).closest('.form-group').find('.chk-all').prop('indeterminate',true); 
		}
		$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
			$(this).prop('checked',true);
			$(this).closest('.form-group').find('.chk-all').prop('checked',true).prop('indeterminate',true);
			$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
				$(this).prop('checked',true);
				$(this).closest('.form-group').find('.chk-all').prop('checked',true).prop('indeterminate',true);
				$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
					$(this).prop('checked',true);
					$(this).closest('.form-group').find('.chk-all').prop('checked',true).prop('indeterminate',true);
					$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
						$(this).prop('checked',true);
						$(this).closest('.form-group').find('.chk-all').prop('checked',true).prop('indeterminate',true);
					});
				});
			});
		});
	} else {
		$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
			$(this).prop('checked',false);
			$(this).closest('.form-group').find('.chk-all').prop('checked',false).prop('indeterminate',false);
			$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
				$(this).prop('checked',false);
				$(this).closest('.form-group').find('.chk-all').prop('checked',false).prop('indeterminate',false);
				$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
					$(this).prop('checked',false);
					$(this).closest('.form-group').find('.chk-all').prop('checked',false).prop('indeterminate',false);
					$('[data-parent="' + $(this).attr('id') + '"]').each(function(){
						$(this).prop('checked',false);
						$(this).closest('.form-group').find('.chk-all').prop('checked',false).prop('indeterminate',false);
					});
				});
			});
		});
	}
	if($(this).closest('.form-group').find('.chk-child:enabled:checked').length > 0) {
		if($(this).closest('.form-group').find('.chk-child:enabled').length == $(this).closest('.form-group').find('.chk-child:enabled:checked').length) {
			$(this).closest('.form-group').find('.chk-all').prop('indeterminate',false).prop('checked',true);
		} else {
			$(this).closest('.form-group').find('.chk-all').prop('indeterminate',true).prop('checked',false);
		}
	} else {
		$(this).closest('.form-group').find('.chk-all').prop('indeterminate',false).prop('checked',false);
	}
});

var xhr_ajax2 = null;
$('.btn-cari').on('click',function(){
	getData();
})
function getData(){
	var data_post = {
		kode_anggaran 	: $('#filter_anggaran option:selected').val(),
		kode_cabang 	: $('#cabang option:selected').val(),
		menu 			: $('#menu option:selected').val(),
	};

	if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/finish/data';
    xhr_ajax2 = $.ajax({
		url 	: page,
		data 	: data_post,
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax2 = null;
			$('.div-body').html(response.view);
			checkSubData();
			menu_finish();
			cLoader.close();
		}
	});
}
$(document).on('click','.ck-all',function(){
	var id = $(this).attr('data-id');
	if($(this).is(':checked')) {
		$('.div-body').find('[data-id="'+id+'"]').prop('checked',true);
	}else{
		$('.div-body').find('[data-id="'+id+'"]').prop('checked',false);
	}
});
$(document).on('click','.d-ck-child',function(){
	var cab_id 		= $(this).attr('data-cab_id');
	var cab_parent	= $(this).attr('data-cab_parent');
	if($(this).is(':checked')) {
		$.each($('.div-body').find('[data-cab_parent="'+cab_id+'"]'),function(){
			$(this).prop('checked',true).trigger('change');
			$.each($('.div-body').find('[data-cab_parent="'+$(this).attr('data-cab_id')+'"]'),function(){
				$(this).prop('checked',true).trigger('change');
				$.each($('.div-body').find('[data-cab_parent="'+$(this).attr('data-cab_id')+'"]'),function(){
					$(this).prop('checked',true).trigger('change');
					$.each($('.div-body').find('[data-cab_parent="'+$(this).attr('data-cab_id')+'"]'),function(){
						$(this).prop('checked',true).trigger('change');
					})
				})
			})
		})
	}else{
		$.each($('.div-body').find('[data-cab_parent="'+cab_id+'"]'),function(){
			$(this).prop('checked',false);
			$.each($('.div-body').find('[data-cab_parent="'+$(this).attr('data-cab_id')+'"]'),function(){
				$(this).prop('checked',false);
				$.each($('.div-body').find('[data-cab_parent="'+$(this).attr('data-cab_id')+'"]'),function(){
					$(this).prop('checked',false);
					$.each($('.div-body').find('[data-cab_parent="'+$(this).attr('data-cab_id')+'"]'),function(){
						$(this).prop('checked',true).trigger('change');
					})
				})
			})
		})
	}
})

$('.btn-save').click(function(){
	var msg 	= lang.anda_yakin_menyetujui;
	cConfirm.open(msg,'save_perubahan');
});
function save_perubahan(){
	var cabang 	 = $('#cabang option:selected').val();
	var anggaran = $('#filter_anggaran option:selected').val();
	var menu 	 = $('#menu option:selected').val();

	$.ajax({
		url : base_url + 'transaction/finish/save_perubahan/'+anggaran+'/'+cabang+'/'+menu,
		data 	: {
			ck : JSON.stringify(get_data_post())
		},
		type : 'post',
		success : function(response) {
			cAlert.open('','success','getData');
		}
	})
}
function get_data_post(){
	const data = {};
	$.each($('.div-body').find('.d-ck-child'),function(k,v){
		if($(this).is(':checked')) {
			var id 	= $(this).attr('data-id');
			var val = $(this).val();
			if(!data[id]){
				data[id] = [];
			}
			data[id].push(val)
		}
	});
	return data;
}
$('button[type="reset"]').on('click',function(){
	$('.content-body select').val(0).trigger('change');
})
var btn_filter = true;
$('#btn-filter').on('click',function(){
	if(btn_filter){
		btn_filter = false;
		$('.div-filter').hide(300);
		$('#btn-filter').html('Tampilkan Filter');
	}else{
		btn_filter = true;
		$('.div-filter').show(300);
		$('#btn-filter').html('Sembunyikan Filter');
	}
})
