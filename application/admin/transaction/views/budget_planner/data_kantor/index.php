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
    			$arr = [
					// ['btn-save','Save Data','fa-save'],
				    ['btn-export','Export Data','fa-upload'],
				    // ['btn-import','Import Data','fa-download'],
				    // ['btn-template','Template Import','fa-reg-file-alt']
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
	<div class="main-container">
	<div class="row">
		<div class="col-sm-12 col-12">
			<form id="form-command" action="<?php echo base_url('transaction/data_kantor_budget_planner/save'); ?>" data-callback="getData" method="post" data-submit="ajax">
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
						</div>
					</div>
					<br>
					<div class="form-group row">
						<div class="col-sm-9 offset-sm-2">
							<button type="submit" class="btn btn-info"><?= lang('simpan_perubahan') ?></button>
						</div>
					</div>

				</div>
			</div>

			<div class="card mt-3 mb-3">
				<div class="card-header"><?= lang('berita_acara') ?></div>
				<div class="card-body">
					<div class="table-responsive tab-pane fade active show" id="result2">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								tr();
									th(get_view_report(),'','colspan="'.(4+6).'"');
								tr();
									th(lang('no'),'align-middle text-center');
									th(lang('keterangan'),'align-middle text-center','');
									for ($i=3; $i >= 0 ; $i--) {
										$real = '<br> (Real)'; 
										$t = ($tahun->tahun_anggaran - $i);
										if(12 != $tahun->bulan_terakhir_realisasi && $t == $tahun->tahun_terakhir_realisasi):
											th(month_lang($tahun->bulan_terakhir_realisasi).' '.($tahun->tahun_terakhir_realisasi).$real,'align-middle text-center');
										endif;
										
										$key = multidimensional_search($detail_tahun, array(
					                        'tahun' => $t,
					                        'bulan' => 12,
					                    ));
					                    if(strlen($key)>0):
					                    	$real = '<br> ('.$detail_tahun[$key]['singkatan'].')';
					                    endif;

										th(month_lang(12).' '.$t.$real,'align-middle text-center');
										if($i<3):
											th('Pert','align-middle text-center');
										endif;
									}
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="9"');
						table_close();
						?>					
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
</script>