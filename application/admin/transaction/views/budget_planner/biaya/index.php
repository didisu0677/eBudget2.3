<style type="text/css">
.txt_title {
    font-size: 10px !important;
}
</style>
<div class="content-header page-data" data-additional="<?= $access_additional ?>">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb($title); ?>
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
			
    		<?php
    		echo filter_cabang_admin($access_additional,$cabang,['kanpus' => 1]);
    		echo ' <button class="btn btn-success btn-save" href="javascript:;" > '.lang('simpan').' <span class="fa-save"></span></button>';
    		echo ' <button class="btn btn-info btn-keterangan" data-title="'.lang('note').'" href="javascript:;" > '.lang('note').' <span class="fa-plus"></span></button>';
    		$arr = [
			    ['btn-export','Export Data','fa-upload'],
			];
			echo ' '.access_button('',$arr);
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
	<?php $this->load->view($path.'sub_menu'); ?>
</div>
<div class="content-body mt-6">
<?php $this->load->view($path.'sub_menu'); ?>
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center"><?= $title ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
						<?php 
							$bulanMin = $tahun->bulan_terakhir_realisasi - 1;
							table_open('table table-striped table-bordered table-app table-1 table-hover tableFixHead',false);
							thead();
								tr();
									th(lang('coa').' 5','','width="60" rowspan="2" class="text-center align-middle"');
									th(lang('coa'),'','width="60" rowspan="2" class="text-center align-middle"');
									th(lang('keterangan'),'','rowspan="2" class="text-center align-middle" style="width:auto;min-width:230px;padding-top: 10px !important;min-height: 50px;"');

									for ($i = 1; $i <= 12; $i++) {
										$a = array_search($i, array_column($detail_tahun, 'bulan'));
										$column = month_lang($i);
										$column .= '<span class="txt_title">'."<br> (".$detail_tahun[$a]['singkatan']." PD BLN)".'</span>';
										th($column,'','class="text-center" style="min-width:80px;"');
									}

									// for ($i = 1; $i <= 12; $i++) { 
									// 	th(month_lang($i),'','class="text-center" style="min-width:80px;"');		
									// }
									th('','','class="text-center border-none bg-white" style="min-width:80px;"');
									th('Biaya Perbulan','','class="text-center" style="min-width:80px;"');
									th('Biaya Pertahun','','class="text-center" style="min-width:80px;"');
									th('','','class="text-center border-none bg-white" style="min-width:80px;"');
									th('Sistem','','class="text-center" style="min-width:80px;"');
									th('Real '.month_lang($bulanMin),'','class="text-center" style="min-width:80px;"');
									th('Real '.month_lang($tahun->bulan_terakhir_realisasi),'','class="text-center" style="min-width:80px;"');
							
							tbody();
						table_close();

						?>
						</div>
	    			</div>
	    		</div>

	    		<div class="d-kebijakan-fungsi"></div>
	    	</div>
	    </div>
	</div>
</div>

<!-- file -->
<?php
modal_open('modal-form','','modal-lg');
	modal_body();
		form_open(base_url('transaction/biaya/save_file'),'post','form','data-callback="refreshFile"');
		input('hidden','id','id');
		input('hidden','kode_cabang','kode_cabang');
		input('hidden','kode_anggaran','kode_anggaran');
		input('hidden','coa','coa');
	?>
		<div class="form-group row d-file">
			<label class="col-form-label col-sm-3">File <small>Max 5MB</small></label>
			<div class="col-sm-9">
				<button type="button" class="btn btn-info" id="add-file" title="<?= lang('tambah') ?> File"><?= lang('tambah') ?> File</button>
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

<!-- keterangan -->
<?php
modal_open('modal-keterangan','','w-90-per');
	modal_body();
		form_open(base_url('transaction/biaya/save_keterangan'),'post','form','data-callback="refreshKeterangan"');
		input('hidden','id','id');
		input('hidden','kode_cabang','kode_cabang');
		input('hidden','kode_anggaran','kode_anggaran');
		col_init(0,12);
		textarea('','keterangan','','','data-editor');
		echo '<div class="d-keterangan"></div>';
		form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
?>
<script type="text/javascript" src="<?php echo base_url('assets/plugins/ckeditor/ckeditor.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function(){
	resize_window();
	var cabang = $('#filter_cabang').val();
	if(cabang){
		loadData();
	}

});	

$('#filter_anggaran').change(function(){
	loadData();
});

$('#filter_cabang').change(function(){
	loadData();
});

$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
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
});




$(document).on('focus','.edit-bulan',function(){
	$(this).parent().removeClass('edited-bulan');
});
$(document).on('blur','.edit-bulan',function(){
	var tr = $(this).closest('tr');
	if($(this).text() != $(this).attr('data-value')) {
		$(this).addClass('edited-bulan');
	}
	if(tr.find('td.edited').length > 0) {
		tr.addClass('edited-row');
	} else {
		tr.removeClass('edited-row');
	}
});

var xhr_ajax = null;
function loadData(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){
		return false;
	}
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
  	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/biaya/data/';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	cLoader.close();
        	if(!res.status){
        		$('#result1 tbody').html('');
        		$('.d-kebijakan-fungsi').html('');
        		cAlert.open(res.message,'failed');
        		return false;
        	}
            $('#result1 tbody').html(res.table);
            if(res.access_edit){
                $('.btn-save').prop('disabled',false);
                $('.btn-save').show();
            }else{
                $('.btn-save').prop('disabled',true);
                $('.btn-save').hide();
            }
            checkSubData();
            $(document).find('.bg-edited2').find('[contenteditable="true"]').closest('td').css({'background':res.bg_edited2});
            loadData2();

		}
    });
}

var xhr_ajax2 = null;
function loadData2(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){
		return false;
	}
    if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }
  	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/biaya/data2';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax2 = $.ajax({
        url: page,
        type: 'post',
        dataType: 'json',
        success: function(res){
        	xhr_ajax2 = null;
        	cLoader.close();
        	if(!res.status){
        		$('#result1 tbody').html('');
        		$('.d-kebijakan-fungsi').html('');
        		cAlert.open(res.message,'failed');
        		return false;
        	}
            $('.d-kebijakan-fungsi').html(res.view);
            resize_window();
		}
    });
}

$(document).on('click','.btn-save',function(){
	var i = 0;
	$('.edited').each(function(){
		i++;
	});
	// if(i == 0) {
	// 	cAlert.open('tidak ada data yang di ubah');
	// } else {
	// 	var msg 	= lang.anda_yakin_menyetujui;
	// 	if( i == 0) msg = lang.anda_yakin_menolak;
	// 	cConfirm.open(msg,'save_perubahan');        
	// }
	var msg 	= lang.anda_yakin_menyetujui;
	cConfirm.open(msg,'save_perubahan');

});


function save_perubahan() {
	var data_edit = {};
	var data_edit_perbulan = {};
	var data_edit_fix = {};
	var i = 0;
	
	

	$('.edited').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'') * -1;
		i++;
	});
	data_edit_fix['bulan'] = data_edit;

	$('.edited-bulan').each(function(){
		var contentBulan = $(this).children('div');
		if(typeof data_edit_perbulan[$(this).attr('data-id')] == 'undefined') {
			data_edit_perbulan[$(this).attr('data-id')] = {};
		}
		data_edit_perbulan[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'') * -1;
		i++;
	});


	data_edit_fix['perbulan'] = data_edit_perbulan;
	
	var jsonString = JSON.stringify(data_edit_fix);	
	// var tahun_anggaran = $('#filter_anggaran option:selected').val();
	// var coa = $('#filter_coa').val();
	var page = base_url + 'transaction/biaya/save_perubahan';
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
			if(response.status == 'failed'){
				cAlert.open(response.message,'failed');
				return false;
			}
			save_promosi();
			cAlert.open(response,'success','loadData');
		}
	})
}

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



$(document).on('keyup','.textpromosi',function(e){
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
$(document).on('keypress','.textpromosi',function(e){
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




function save_promosi() {
	var data_edit = {};
	var i = 0;
	
	$('.promosi').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		if($(this).attr('data-name') == "keterangan"){
			data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		}else {
			data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'') * 1000;
		}
		
		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);	
	// var tahun_anggaran = $('#filter_anggaran option:selected').val();
	// var coa = $('#filter_coa').val();
	var page = base_url + 'transaction/biaya/save_promosi';
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


// file
$(document).on('click','.btn-file',function(){
	var coa = $(this).attr('data-id');
	getFileView(coa);
})
function refreshFile(){
	var coa = $('#modal-form #coa').val();
	getFileView(coa);
}
var xhr_file_view = null;
function getFileView(coa){
	if( xhr_file_view != null ) {
        xhr_file_view.abort();
        xhr_file_view = null;
    }

    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/biaya/file_view';

    var data_post = {
    	coa : coa,
    	kode_cabang : $('#filter_cabang option:selected').val(),
    	kode_anggaran : $('#filter_anggaran option:selected').val(),
    }

  	xhr_file_view = $.ajax({
        url: page,
        type: 'post',
		data : data_post,
        dataType: 'json',
        success: function(res){
        	xhr_file_view = null;
        	if(!res.status){
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
       		
       		$('#modal-form').modal('show');
       		$('#modal-form .modal-title').text('File '+res.title);
       		$('#modal-form .modal-body').attr('data-title',res.title);

       		$('#additional-file').html('');
       		$('#modal-form #coa').val(data_post.coa);
       		$('#modal-form #kode_cabang').val(data_post.kode_cabang);
       		$('#modal-form #kode_anggaran').val(data_post.kode_anggaran);
       		$('#modal-form #d-none').text('');

       		var length = 0;
       		if(res.list && typeof res.list.id != 'undefined') {
       			$('#modal-form #id').val(res.list.id);
       			length = res.list.file.length;
       			$.each(res.list.file,function(n,z){
       				var key = n.split('--');
       				if(key.length>1){
       					n = key[1];
       				}
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
						+ '<a href="'+base_url+'assets/uploads/biaya_file/'+z+'" target="_blank" class="btn btn-info btn-icon-only"><i class="fa-download"></i></a>'
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
							+ '<div class="col-sm-1 col-3">'
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

// keterangan
$(document).on('click','.btn-keterangan',function(){
	var title = $(this).attr('data-title');
	get_keterangan(title);
})
var xhr_keterangan = null;
function get_keterangan(title){
	var data_post = {
    	kode_cabang : $('#filter_cabang option:selected').val(),
    	kode_anggaran : $('#filter_anggaran option:selected').val(),
    }

    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/biaya/keterangan_view';
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

			$('#modal-keterangan #kode_cabang').val(data_post.kode_cabang);
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
  
    var classnya = ['#result1','.d-kebijakan-fungsi'];
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
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/biaya/export';
    $.redirect(url,post_data,"","_blank");
});
function get_data_table(classnya){
    var arr = [];
    var arr_header = [];
    var no = 0;
    var index_cabang = 0;
    $(classnya).find(" table tr").each(function() {
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
</script>