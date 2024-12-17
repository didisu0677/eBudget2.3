<style type="text/css">
.content-body .select2-container--default .select2-selection--single{
	min-width: auto !important;
	width: auto !important;
}
</style>
<div class="content-header page-data" data-additional="<?= $access_additional ?>" data-type="divisi">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php
			input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
			?>
			<label class=""><?php echo lang('anggaran'); ?>  &nbsp</label>
			<select class="select2 infinity custom-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select>
			<button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>
    	</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-6">
				<div class="card">
					<div class="card-header text-center div-filter">Filter Data</div>
					<div class="card-body div-filter">
						<?php
							col_init(3,6);
							if($status_group == 1):
								select2(lang('cabang_induk'),'filter_cabang_induk','',$cabang,'id','nama_cabang','','data-type="divisi"');
								select2(lang('cabang'),'filter_cabang','',[]);
							else:
								select2(lang('cabang'),'filter_cabang','',$cabang,'kode_cabang','nama_cabang',user('kode_cabang'));
							endif;
							col_init(3,4);
							select2(lang('status'),'status','',$status,'value','name');
							select2(lang('bulan'),'bulan','',$bulan,'value','name');
						?>
						<div class="form-group row">
							<div class="col-sm-4 offset-sm-3">
								<button type="submit" class="btn btn-info"><?= lang('cari') ?></button>
								<button type="reset" class="btn btn-secondary">Reset</button>
								<button type="button" id="btn-export" class="btn btn-success">Export</button>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<div class="text-right"><a id="btn-filter" href="javascript:;">Sembunyikan Filter</a></div>
					</div>
				</div>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center div-filter"><?= lang('monitoring_rkf2') ?></div>
					<div class="card-body">
						<div class="table-responsive tab-pane fade active show height-window" id="result1">
							<?php
							table_open('table table-striped table-bordered table-app table-hover',false);
								thead('sticky-top');
									tr();
										th(lang('no'),'','width="60" class="text-center align-middle"');
										th(lang('kebijakan_umum_direksi'),'','style="min-width:250px" rowspan="2" class="text-center align-middle"');
										th(lang('program_kerja'),'','class="text-center" style="min-width:150px"');
										th(lang('produk_aktivitas_baru'),'','class="text-center" style="min-width:150px"');
										th(lang('perspektif'),'','class="text-center" style="min-width:150px"');
										th(lang('status_program'),'','class="text-center" style="min-width:150px"');
										th(lang('skala_program'),'','class="text-center" style="min-width:150px"');
										th(lang('tujuan'),'','class="text-center" style="min-width:150px"');
										th(lang('output'),'','class="text-center" style="min-width:150px"');
										th(lang('target_financial'),'','class="text-center" style="min-width:150px"');
										th(lang('anggaran'),'','style="min-width:150px" class="text-center"');
										th(lang('anggaran_perbulan'),'','style="min-width:150px" class="text-center"');
										th(lang('pos_total_anggaran'),'','style="min-width:150px" class="text-center"');
										th(lang('divisi_terkait'),'','style="min-width:250px" class="text-center"');
										th(lang('pic'),'','style="min-width:250px" class="text-center"');
										th(lang('status_program_kerja'),'','style="min-width:150px" class="text-center"');
										th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
										th(lang('bulan'),'','style="min-width:150px" class="text-center"');
										th(lang('uraian'),'','style="min-width:200px" class="text-center"');
										th(lang('bobot'),'','style="min-width:200px" class="text-center"');
										th(lang('status_progres'),'','style="min-width:150px" class="text-center"');
										th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
										th(lang('aktivitas_penjelasan'),'','style="min-width:150px" class="text-center"');
										th(lang('keterangan').' 1','','style="min-width:150px" class="text-center"');
										th(lang('keterangan').' 2','','style="min-width:150px" class="text-center"');
								tbody();
								tr();
									th(lang('data_not_found'),'','colspan="16"');
							table_close();
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center div-filter">RKF Divisi Terkait</div>
					<div class="card-body">
						<div class="table-responsive tab-pane fade active show height-window" id="result2">
							<?php
							table_open('table table-striped table-bordered table-app table-hover',false);
								thead('sticky-top');
									tr();
										th(lang('no'),'','width="60" class="text-center align-middle"');
										th(lang('kebijakan_umum_direksi'),'','style="min-width:250px" rowspan="2" class="text-center align-middle"');
										th(lang('program_kerja'),'','class="text-center" style="min-width:150px"');
										th(lang('produk_aktivitas_baru'),'','class="text-center" style="min-width:150px"');
										th(lang('perspektif'),'','class="text-center" style="min-width:150px"');
										th(lang('status_program'),'','class="text-center" style="min-width:150px"');
										th(lang('skala_program'),'','class="text-center" style="min-width:150px"');
										th(lang('tujuan'),'','class="text-center" style="min-width:150px"');
										th(lang('output'),'','class="text-center" style="min-width:150px"');
										th(lang('target_financial'),'','class="text-center" style="min-width:150px"');
										th(lang('anggaran'),'','style="min-width:150px" class="text-center"');
										th(lang('anggaran_perbulan'),'','style="min-width:150px" class="text-center"');
										th(lang('pos_total_anggaran'),'','style="min-width:150px" class="text-center"');
										th(lang('divisi_terkait'),'','style="min-width:250px" class="text-center"');
										th(lang('pic'),'','style="min-width:250px" class="text-center"');
										th(lang('status_program_kerja'),'','style="min-width:150px" class="text-center"');
										th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
										th(lang('bulan'),'','style="min-width:150px" class="text-center"');
										th(lang('uraian'),'','style="min-width:200px" class="text-center"');
										th(lang('bobot'),'','style="min-width:200px" class="text-center"');
										th(lang('status_progres'),'','style="min-width:150px" class="text-center"');
										th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
										th(lang('aktivitas_penjelasan'),'','style="min-width:150px" class="text-center"');
										th(lang('keterangan').' 1','','style="min-width:150px" class="text-center"');
										th(lang('keterangan').' 2','','style="min-width:150px" class="text-center"');
								tbody();
								tr();
									th(lang('data_not_found'),'','colspan="16"');
							table_close();
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- file -->
<?php
modal_open('modal-form','','modal-lg');
	modal_body();
		form_open(base_url('transaction/monthly_performance_pkf/save_file'),'post','form-file','data-callback="refreshFile"');
		input('hidden','id','id');
		input('hidden','id_input_rkf_detail','id_input_rkf_detail');
	?>
		<div class="form-group row d-file">
			<label class="col-form-label col-sm-3">File <small>Max 5MB</small></label>
			<div class="col-sm-9">
				<button type="button" class="btn btn-info" id="add-file" title="Tambah File">Tambah File</button>
			</div>
		</div>
		<div id="additional-file" class="mb-2"></div>
		<div id="d-none"></div>
	<?php
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
?>

<form action="<?php echo base_url('upload/file/datetime'); ?>" class="hidden">
	<input type="hidden" name="name" value="field_document">
	<input type="hidden" name="token" value="<?php echo encode_id([user('id'),(time() + 900)]); ?>">
	<input type="file" name="document" id="upl-file">
</form>
<script type="text/javascript">
$(function(){
	$('.content-body #status').val(0).trigger('change');
	$('.content-body #bulan').val(0).trigger('change');
	resize_window();
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
$('button[type="reset"]').on('click',function(){
	$('.content-body #status').val(0).trigger('change');
	$('.content-body #bulan').val(0).trigger('change');
})
var xhr_ajax = null;
$('.div-filter button[type="submit"]').on('click',function(){
	loadData();

})
function loadData(){
	var kode_anggaran 	= $('#filter_anggaran option:selected').val();
	var cabang 		  	= $('#filter_cabang option:selected').val();
	var status 	  		= $('#status option:selected').val();
	var bulan 	  		= $('#bulan option:selected').val();

	var data_post = {
		kode_anggaran 	: kode_anggaran,
		cabang 			: cabang,
		status 			: status,
		bulan 			: bulan,
	};

	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/monthly_performance_pkf/data';
    xhr_ajax = $.ajax({
		url 	: page,
		data 	: data_post,
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			$('#result1 tbody').html(response.table);
			$('#result2 tbody').html(response.table2);

			if(!response.access_edit) {	
				$(".btn-save").prop("disabled", true);	
				$(".btn-save").hide();	
			}else{
				$(".btn-save").prop("disabled", false);	
				$(".btn-save").show();	
			}

			cLoader.close();
		}
	});
}
$('#btn-export').on('click',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));

	var kode_anggaran 	= $('#filter_anggaran option:selected').val();
	var cabang 		  	= $('#filter_cabang option:selected').val();
	var status 	  		= $('#status option:selected').val();
	var bulan 	  		= $('#bulan option:selected').val();
	
	var data_post = {
		kode_anggaran 	: kode_anggaran,
		cabang 			: cabang,
		status 			: status,
		bulan 			: bulan,
		export 			: true,
		"csrf_token"    : x[0],
	};
    var url = base_url + 'transaction/monthly_performance_pkf/data';
    $.redirect(url,data_post,"","_blank");
})
var url_request 	= '';
var post_dt_page 	= '';
$(document).on('click','.btn-approve',function(){
	var dt_id 	= $(this).attr('data-id');
	var status  = $(this).attr('data-status');
	var message = $(this).attr('data-message');
	post_dt_page = $(this).attr('data-page');
	if(dt_id){
		del_id 			= dt_id+"-"+$('#filter_cabang option:selected').val()+"-"+status;
		url_request 	= base_url+"transaction/monthly_performance_pkf/change_status";
		cConfirm.open('Apakah yakin mengubah status "'+message+'" data ini'+ '?','change_status');
	}
})
function change_status(){
	$.ajax({
		url : url_request,
		data 	: {
			id : del_id,
			page: post_dt_page,
		},
		type : 'post',
		success : function(response) {
			cAlert.open(response.message,response.status,response.url);
		}
	})
}

$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
});
$(document).on('blur','.edit-value, .edit-text',function(){
	var tr = $(this).closest('tr');
	if($(this).text() != $(this).attr('data-value')) {
		$(this).addClass('edited');
	}
	if(tr.find('td.edited').length > 0) {
		tr.addClass('edited-row');
	} else {
		tr.removeClass('edited-row');
	}
});
$(document).on('keyup','.edit-value',function(e){
	var wh 			= e.which;
	if((48 <= wh && wh <= 57) || (96 <= wh && wh <= 105) || wh == 8) {
		if($(this).text() == '') {
			$(this).text('');
		} else {
			var n = parseInt($(this).text().replace(/[^0-9\-]/g,''),10);
		    $(this).text(n.toLocaleString());
		    var selection = window.getSelection();
			var range = document.createRange();
			selection.removeAllRanges();
			range.selectNodeContents($(this)[0]);
			range.collapse(false);
			selection.addRange(range);
			$(this)[0].focus();
		}
	}
});
$(document).on('keypress','.edit-value',function(e){
	var wh 			= e.which;
	if (e.shiftKey) {
		if(wh == 0) return true;
	}
	if(e.metaKey || e.ctrlKey) {
		if(wh == 86 || wh == 118) {
			$(this)[0].onchange = function(){
				$(this)[0].innerHTML = $(this)[0].innerHTML.replace(/[^0-9\-]/g, '');
			}
		}
		return true;
	}
	if(wh == 0 || wh == 8 || wh == 45 || (48 <= wh && wh <= 57) || (96 <= wh && wh <= 105)) 
		return true;
	return false;
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

		var vfield = ['keterangan','keterangan2','keterangan3'];

		if (jQuery.inArray($(this).attr('data-name'),vfield) != -1) {
			data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		}else{
			data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'');
		}

		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);		
	$.ajax({
		url : base_url + 'transaction/monthly_performance_pkf/save_perubahan',
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

// file
$(document).on('click','.btn-file',function(){
	var id_input_rkf_detail = $(this).attr('data-id');
	getFileView(id_input_rkf_detail);
})
function refreshFile(){
	var id_input_rkf_detail = $('#modal-form #id_input_rkf_detail').val();
	getFileView(id_input_rkf_detail);
}
var xhr_file_view = null;
function getFileView(id_input_rkf_detail){
	if( xhr_file_view != null ) {
        xhr_file_view.abort();
        xhr_file_view = null;
    }

    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/monthly_performance_pkf/file_view';

    var data_post = {
    	id_input_rkf_detail : id_input_rkf_detail,
    }

  	xhr_file_view = $.ajax({
        url: page,
        type: 'post',
		data : data_post,
        dataType: 'json',
        success: function(res){
        	xhr_file_view = null;
       		
       		$('#modal-form').modal('show');
       		$('#modal-form .modal-title').text('File '+res.title);
       		$('#modal-form .modal-body').attr('data-title',res.title);

       		$('#additional-file').html('');
       		$('#modal-form #id_input_rkf_detail').val(data_post.id_input_rkf_detail);
       		$('#modal-form #d-none').text('');

       		var length = 0;
       		if(res.list && typeof res.list.id != 'undefined') {
       			$('#modal-form #id').val(res.list.id);
       			length = res.list.file.length;
       			$.each(res.list.file,function(n,z){
       				var btn_remove = '';
       				var v_readonly = ' data-readonly="true"';
       				if(res.access_edit){
       					btn_remove = '<button type="button" class="btn btn-danger btn-remove btn-block btn-icon-only"><i class="fa-times"></i></button>';
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
						+ '<a href="'+base_url+'assets/uploads/input_rkf_detail_file/'+z+'" target="_blank" class="btn btn-info btn-icon-only"><i class="fa-download"></i></a>'
						+ '</div>'
						+ '</div>'
						+ '</div>'
						+ '<div class="col-sm-2 col-3">'
						+ btn_remove
						+ '</div>'
						+ '</div>';
					$('#additional-file').append(konten);
				});
       		}else{
       			$('#modal-form #id').val('');
       		}

       		if(res.access_edit){
       			$('.d-file').show();
       			$('#modal-form button[type="submit"]').show();
       		}else{
       			$('.d-file').hide();
       			$('#modal-form button[type="submit"]').hide();
       			if(length<=0){
       				var item = `<div class="form-group row d-file">
						<label class="col-form-label col-sm-9">Data Tidak Ditemukan</label>
					</div>`;
					$('#modal-form #d-none').append(item);
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
							+ '<div class="col-sm-2 col-3">'
							+ '<button type="button" class="btn btn-danger btn-remove btn-block btn-icon-only"><i class="fa-times"></i></button>'
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
$(document).on('click','.btn-remove',function(){
	$(this).closest('.form-group').remove();
});
</script>
