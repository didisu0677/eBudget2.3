<div class="content-header page-data" data-additional="<?= $access_additional ?>">
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
  	
    		<?php
    			echo filter_cabang_admin($access_additional,$cabang,['kanpus' => 1]);
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
</div>

<div class="content-body mt-6">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-12 col-12">
				<form id="form-command" action="<?php echo base_url('transaction/'.$controller.'/save'); ?>" data-callback="getData" method="post" data-submit="ajax">
				<br>
					<div class="card">
		    			<div class="card-header"><?php echo lang('data_kantor'); ?></div>
						<div class="card-body">
							<div class="row">
								<div class="col-sm-9">
									<input type="hidden" id="id" name="id">
									<div class="form-group row">
										<label class="col-sm-2 col-form-label required" for="kode_cabang"><?= lang('kode_cabang') ?></label>
										<div class="col-sm-6">
											<input type="text" name="kode_cabang" id="kode_cabang" class="form-control" autocomplete="off" data-validation="required|unique" readonly>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label required" for="nama_kantor"><?= lang('nama_kantor') ?></label>
										<div class="col-sm-6">
											<input type="text" name="nama_kantor" id="nama_kantor" class="form-control" autocomplete="off" data-validation="required" readonly>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label required" for="nama_pimpinan"><?= lang('pimpinan') ?></label>
										<div class="col-sm-6">
											<input type="text" name="nama_pimpinan" id="nama_pimpinan" class="form-control" autocomplete="off" data-validation="required">
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label required" for="nama_pimpinan"><?= lang('no_hp_pimpinan') ?></label>
										<div class="col-sm-6" bis_skin_checked="1">
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
										<div class="col-sm-6">
											<input type="text" name="tgl_mulai_menjabat" id="tgl_mulai_menjabat" class="form-control dp" autocomplete="off" data-validation="required">
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label required" for="nama_cp"><?= lang('nama_cp') ?></label>
										<div class="col-sm-6">
											<input type="text" name="nama_cp" id="nama_cp" class="form-control" autocomplete="off" data-validation="required">
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label required" for="no_hp_cp"><?= lang('no_hp_cp') ?></label>
										<div class="col-sm-6" bis_skin_checked="1">
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
										<div class="col-sm-6">
											<input type="text" name="nama_cp2" id="nama_cp2" class="form-control" autocomplete="off">
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label" for="no_hp_cp"><?= lang('no_hp_cp').' 2' ?></label>
										<div class="col-sm-6" bis_skin_checked="1">
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
										<div class="col-sm-6">
											<input type="text" name="email_Cp" id="email_Cp" class="form-control" autocomplete="off" data-validation="required|email">
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 col-form-label" for="email_lainnya"><?= lang('email_kantor_lainnya') ?></label>
										<div class="col-sm-6">
											<input type="text" name="email_lainnya" id="email_lainnya" class="form-control" autocomplete="off" data-validation="email">
										</div>
									</div>
									<div class="form-group row">
										<div class="col-sm-8 offset-sm-2">
											<button type="submit" class="btn btn-info"><?= lang('simpan_perubahan') ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var xhr_ajax = null;
var controller = '<?= $controller ?>';
$(document).ready(function(){
	getData();
});
$('#filter_cabang').on('change',function(){
	getData();
});

function getData(){
	var kode_cabang = $('#filter_cabang option:selected').val();
	var cabang = $('#filter_cabang').val();
	if(!cabang){ return ''; }
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();
		
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

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
			}
		});
	}
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
</script>