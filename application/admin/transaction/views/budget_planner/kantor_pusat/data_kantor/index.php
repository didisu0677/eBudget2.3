<style type="text/css">
.custom-nav li{
	max-width: 100% !important;
}
</style>
<div class="content-header  page-data" data-additional="<?= $access_additional ?>" data-type="divisi" data-status_group="<?= $status_group ?>">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php
			input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
			echo $option;
			$arr = [
				// ['btn-save','Save Data','fa-save'],
			    ['btn-export','Export Data','fa-upload'],
			    // ['btn-import','Import Data','fa-download'],
			    // ['btn-template','Template Import','fa-reg-file-alt']
			];
			echo ' '.access_button('',$arr,true);
			?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php $this->load->view($sub_menu); ?>
</div>
<div class="content-body mt-6"> 
	<?php $this->load->view($sub_menu); ?>
	<div class="main-container container">
		<div class="row mt-3">
			<div class="col-sm-9">
				<h2 class="text-center">Data Kantor</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-9">
				<form id="form-command" action="<?php echo base_url('transaction/plan_data_kantor/save'); ?>" data-callback="getData" method="post" data-submit="ajax">
					<input type="hidden" id="id" name="id">
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="kode_cabang"><?= lang('kode_cabang') ?></label>
						<div class="col-sm-8">
							<input type="text" name="kode_cabang" id="kode_cabang" class="form-control" autocomplete="off" data-validation="required|unique" readonly>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="nama_kantor"><?= lang('nama_kantor') ?></label>
						<div class="col-sm-8">
							<input type="text" name="nama_kantor" id="nama_kantor" class="form-control" autocomplete="off" data-validation="required" readonly>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="nama_pimpinan"><?= lang('pimpinan') ?></label>
						<div class="col-sm-8">
							<input type="text" name="nama_pimpinan" id="nama_pimpinan" class="form-control" autocomplete="off" data-validation="required">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="nama_pimpinan"><?= lang('no_hp_pimpinan') ?></label>
						<div class="col-sm-8" bis_skin_checked="1">
							<div class="input-group" bis_skin_checked="1">
								<select style="max-width:100px" class="form-control custom-select" data-validation="">
								<option value="62">+62</option>
								</select>
								<input type="text" name="no_hp_pimpinan" id="no_hp_pimpinan" class="form-control" autocomplete="off" data-validation="required">
								<div class="input-group-append" bis_skin_checked="1">
									<button type="button" data-id="no_hp_pimpinan" class="btn btn-default btn-wa"><i class="fa fa-whatsapp"></i></button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="tgl_mulai_menjabat"><?= lang('mulai_menjabat') ?></label>
						<div class="col-sm-8">
							<input type="text" name="tgl_mulai_menjabat" id="tgl_mulai_menjabat" class="form-control dp" autocomplete="off" data-validation="required">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="nama_cp"><?= lang('nama_cp') ?></label>
						<div class="col-sm-8">
							<input type="text" name="nama_cp" id="nama_cp" class="form-control" autocomplete="off" data-validation="required">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="no_hp_cp"><?= lang('no_hp_cp') ?></label>
						<div class="col-sm-8" bis_skin_checked="1">
							<div class="input-group" bis_skin_checked="1">
								<select style="max-width:100px" class="form-control custom-select" data-validation="">
								<option value="62">+62</option>
								</select>
								<input type="text" name="no_hp_cp" id="no_hp_cp" class="form-control" autocomplete="off" data-validation="required">
								<div class="input-group-append" bis_skin_checked="1">
									<button type="button" data-id="no_hp_cp" class="btn btn-default btn-wa"><i class="fa fa-whatsapp"></i></button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label" for="nama_cp"><?= lang('nama_cp').' 2' ?></label>
						<div class="col-sm-8">
							<input type="text" name="nama_cp2" id="nama_cp2" class="form-control" autocomplete="off">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label" for="no_hp_cp"><?= lang('no_hp_cp').' 2' ?></label>
						<div class="col-sm-8" bis_skin_checked="1">
							<div class="input-group" bis_skin_checked="1">
								<select style="max-width:100px" class="form-control custom-select" data-validation="">
								<option value="62">+62</option>
								</select>
								<input type="text" name="no_hp_cp2" id="no_hp_cp2" class="form-control" autocomplete="off">
								<div class="input-group-append" bis_skin_checked="1">
									<button type="button" data-id="no_hp_cp2" class="btn btn-default btn-wa"><i class="fa fa-whatsapp"></i></button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label required" for="email_Cp"><?= lang('email_kantor') ?></label>
						<div class="col-sm-8">
							<input type="text" name="email_Cp" id="email_Cp" class="form-control" autocomplete="off" data-validation="required|email">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label" for="email_lainnya"><?= lang('email_kantor_lainnya') ?></label>
						<div class="col-sm-8">
							<input type="text" name="email_lainnya" id="email_lainnya" class="form-control" autocomplete="off" data-validation="email">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-sm-10 offset-sm-2">
							<button type="submit" class="btn btn-info">Simpan Perubahan</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var status_group = 0;
$(document).ready(function(){
	var page_data = $('.page-data').data();
	if(page_data && page_data.status_group == 1){
		status_group = 1;
	}
	if(status_group == 1){
		$('.l-cabang').hide();
		$('#filter_cabang').next(".select2-container").hide();
	}
	getData();
});
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
function getData(){
	var kode_cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		kode_cabang = $('#filter_cabang option:selected').val();
	}
	if(!kode_cabang){ return ''; }
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/plan_data_kantor/get_data';
	page 	+= '/'+kode_cabang;
	$.ajax({
		url 	: page,
		data 	: { status_group : status_group },
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			cLoader.close();
			if(!response.status){
				$('#form-command input').val('');
				cAlert.open(response.message,'failed');
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
			}else{
				$('#kode_cabang').val(kode_cabang);
			}
			if(response.access_edit){
				$('#form-command input').prop('disabled',false);
				$('#form-command button').prop('disabled',false);
				$('#form-command button').show();
			}else{
				$('#form-command input').prop('disabled',true);
				$('#form-command button').prop('disabled',true);
				$('#form-command button').hide();
			}
		}
	});
}
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
	var kode_cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		kode_cabang = $('#filter_cabang option:selected').val();
	}
	if(!kode_cabang){ return ''; }

	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var post_data = {
    	"kode_cabang" 		: kode_cabang,
    	"status_group" 		: status_group,
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/plan_data_kantor/export';
    $.redirect(url,post_data,"","_blank");
});
</script>