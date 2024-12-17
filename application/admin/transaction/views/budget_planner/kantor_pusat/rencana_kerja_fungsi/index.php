<style type="text/css">
.multiple .select2-container{
	width: 200px !important;
}
form table .select2-selection--single {
  height: 100% !important;
  min-height:30px;
}
form table .select2-selection__rendered{
  word-wrap: break-word !important;
  text-overflow: inherit !important;
  white-space: normal !important;
}
.custom-nav li{
	max-width: 100% !important;
}
</style>
<div class="content-header  page-data" data-additional="<?= $access_additional ?>" data-type="divisi">
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
    			echo $option.' ';
    			echo ' <button class="btn btn-info btn-keterangan" data-title="'.lang('note').'" href="javascript:;" > '.lang('note').' <span class="fa-plus"></span></button>';
				$arr = [
					// ['btn-save','Save Data','fa-save'],
				    ['btn-export','Export Data','fa-upload'],
				    ['btn-import','Import Data','fa-download'],
				    ['btn-act-template','Template Import','fa-file-alt']
				];
				echo access_button('',$arr); 
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
	<?php $this->load->view($sub_menu); ?>
</div>
<div class="content-body mt-6">
<?php $this->load->view($sub_menu); ?>
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center"><?= $title ?> <br>(<?= get_view_report() ?>)</div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="tbl_rkf" data-height="100">
	    					<?php
	    					$thn_sebelumnya = user('tahun_anggaran') -1;
							table_open('',true,'','','data-table="tbl_m_produk"');
								thead('sticky-top');
									tr();
										tr();
										th(lang('no'),'','width="60" rowspan="2" class="text-center align-middle"');
										th('KEBIJAKAN UMUM DIREKSI','','style="min-width:250px" rowspan="2" class="text-center align-middle"');
										th('PROGRAM KERJA','','class="text-center" style="min-width:150px"');
										th('PRODUK / AKTIVITAS BARU','','class="text-center" style="min-width:150px"');
										th('PERSPEKTIF','','class="text-center" style="min-width:150px"');
										th('STATUS PROGRAM','','class="text-center" style="min-width:150px"');
										th('SKALA PROGRAM','','class="text-center" style="min-width:150px"');
										th('TUJUAN','','class="text-center" style="min-width:150px"');
										th('OUTPUT','','class="text-center" style="min-width:150px"');
										th('TARGET FINANSIAL','','class="text-center" style="min-width:150px"');
										th('Anggaran','','style="min-width:150px" class="text-center"');
										th('Divisi Terkait','','style="min-width:250px" class="text-center"');
										th('Bulan','','style="min-width:150px" class="text-center"');
										th('Uraian','','style="min-width:150px" class="text-center"');
										th('Bobot','','style="min-width:150px" class="text-center"');
										th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
								tbody();
							table_close();
	    					?>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>

		<div class="row mt-2">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center"><?= $title.' Divisi Terkait' ?> <br>(<?= get_view_report() ?>)</div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="tbl_rkf_terkait" data-height="100">
	    					<?php
	    					$thn_sebelumnya = user('tahun_anggaran') -1;
							table_open('',true);
								thead('sticky-top');
									tr();
										tr();
										th(lang('no'),'','width="60" rowspan="2" class="text-center align-middle"');
										th('KEBIJAKAN UMUM DIREKSI','','style="min-width:250px" rowspan="2" class="text-center align-middle"');
										th('PROGRAM KERJA','','class="text-center" style="min-width:150px"');
										th('PRODUK / AKTIVITAS BARU','','class="text-center" style="min-width:150px"');
										th('PERSPEKTIF','','class="text-center" style="min-width:150px"');
										th('STATUS PROGRAM','','class="text-center" style="min-width:150px"');
										th('SKALA PROGRAM','','class="text-center" style="min-width:150px"');
										th('TUJUAN','','class="text-center" style="min-width:150px"');
										th('OUTPUT','','class="text-center" style="min-width:150px"');
										th('TARGET FINANSIAL','','class="text-center" style="min-width:150px"');
										th('Anggaran','','style="min-width:150px" class="text-center"');
										th('Divisi Terkait','','style="min-width:250px" class="text-center"');
										th('Bulan','','style="min-width:150px" class="text-center"');
										th('Uraian','','style="min-width:150px" class="text-center"');
										th('Bobot','','style="min-width:150px" class="text-center"');
										th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
								tbody();
							table_close();
	    					?>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>
</div>

<?php
modal_open('modal-form','','modal-lg w-90-per',' data-openCallback="formOpen" style="overflow: initial !important;"');
	modal_body('style-select2');
		form_open(base_url('transaction/plan_rencana_kerja_fungsi/save'),'post','form'); 
			col_init(2,4);
				input('hidden','id','id');
				input('text',lang('tahun'),'tahun_anggaran','',user('tahun_anggaran'),'disabled');
				echo cabang($cabang);
			col_init(2,9);
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();

modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('transaction/plan_rencana_kerja_fungsi/import'),'post','form-import');
			col_init(3,9);
			input('hidden','kode_cabang','kode_cabang');
			input('hidden','kode_anggaran','kode_anggaran');
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			select2('Dalam bentuk','currency','required');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();

function cabang($cabang_input){
	$option = '';
	foreach($cabang_input as $b){
		if($b['kode_cabang'] == user('kode_cabang'))  $selected = ' selected'; else $selected = '';
		$option .= '<option value="'.$b['kode_cabang'].'"'.$selected.'>'.$b['nama_cabang'].'</option>';
	}
	$item = '<div class="form-group row">
		<label class="col-form-label col-md-2">'.lang('cabang').' &nbsp</label>
		<div class="col-md-4 col-9 mb-1 mb-md-0">	
			<select class="select2 infinity custom-select" id="kode_cabang" name="kode_cabang">'.$option.'</select>   
		</div>
	</div>';
	$item .= '<div class="form-group row">
		<label class="col-form-label col-md-12">Note : Total bobot max 100 dari 12 Bulan</label>
	</div>';
	$item .= '<div class="card mb-2">
			<div id="result2" class="mb-3">	
			<div class="table-responsive height-window">
			    <table class="table table-bordered" id="result2">
					<thead class="sticky-top">
						<tr>
							<th class="text-center bg-grey">KEBIJAKAN UMUM DIREKSI</th>
							<th class="text-center bg-grey">PROGRAM KERJA</th>
							<th class="text-center bg-grey">PRODUK / AKTIVITAS BARU</th>
							<th class="text-center bg-grey">PERSPEKTIF</th>
							<th class="text-center bg-grey">STATUS PROGRAM</th>
							<th class="text-center bg-grey">SKALA PROGRAM</th>
							<th class="text-center bg-grey">TUJUAN</th>
							<th class="text-center bg-grey">OUTPUT</th>
							<th class="text-center bg-grey">TARGET FINANSIAL</th>
							<th class="text-center bg-grey">Anggaran</th>
							<th class="text-center bg-grey">Divisi Terkait</th>
							<th class="text-center bg-grey">PIC</th>
							<th class="text-center bg-grey">Jadwal Bulan</th>
							<th class="text-center bg-grey">Uraian</th>
							<th class="text-center bg-grey">Bobot</th>
							<th width="10">
								<button type="button" class="btn btn-sm btn-icon-only btn-success btn-add-item"><i class="fa-plus"></i></button>
							</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			</div>
		</div>';
	return $item;
}
?>

<!-- keterangan -->
<?php
modal_open('modal-keterangan','','w-90-per');
	modal_body();
		form_open(base_url('transaction/plan_rencana_kerja_fungsi/save_keterangan'),'post','form','data-callback="refreshKeterangan"');
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
var dt_kebijakan_umum = '';
var dt_perspektif 	  = '';
var dt_status_program = '<option></option><option>Baru</option><option>Carry Over</option>';
var dt_skala_program  = '';
var dt_index = 0;
var response_data = [];
var arr_bulan = [];
var option_divisi = '';
var dt_pic 	  = '';
$(document).ready(function () {
	resize_window();
	getData();
	getDivisi();
	getPegawai();
});
function getDivisi(){
	$.ajax({
		url : base_url + 'api/divisi_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			option_divisi = response.data;
		}
	});
}
function getPegawai(){
	$.ajax({
		url : base_url + 'api/pegawai_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			dt_pic = response.data;
		}
	});
}

$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){getData();});
function getData() {
	var cabang = $('#filter_cabang').val();
	if(!cabang){ return ''; }
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/plan_rencana_kerja_fungsi/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			response_data = [];
			$('#tbl_rkf tbody').html(response.table);
			$('#tbl_rkf_terkait tbody').html(response.table2);
			arr_bulan = response.arr_bulan;
			cLoader.close();
			cek_autocode();
			fixedTable();
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
function formOpen() {
	var c_cabang 		= $('#filter_cabang option:selected').val();
	var c_cabang_name 	= $('#filter_cabang option:selected').text();
	$('#kode_cabang').empty();
	$('#kode_cabang').append('<option value="'+c_cabang+'">'+c_cabang_name+'</option>').trigger('change');

	dt_index = 0;
	response_data = response_edit;
	$('#result2 tbody').html('');
	var cabang = $('#filter_cabang option:selected').val();
	$('#kode_cabang').val(cabang).trigger('change');
	get_perspektif();	
}
function get_perspektif(){
	if(proccess) {
		$.ajax({
			url : base_url + 'transaction/plan_rencana_kerja_fungsi/get_perspektif',
			data : {},
			type : 'POST',
			success	: function(response) {
				dt_perspektif = response;
				get_kebijakan_umum();
			}
		});
	}
}
function get_kebijakan_umum(){
	if(proccess) {
		$.ajax({
			url : base_url + 'transaction/plan_rencana_kerja_fungsi/get_kebijakan_umum',
			data : {},
			type : 'POST',
			success	: function(response) {
				dt_kebijakan_umum = response;
				get_skala_program();		
			}
		});
	}
}
function get_skala_program(){
	if(proccess) {
		$.ajax({
			url : base_url + 'transaction/plan_rencana_kerja_fungsi/get_skala_program',
			data : {},
			type : 'POST',
			success	: function(response) {
				dt_skala_program = response;
				add_item();	
				if(typeof response_data.detail != 'undefined') {
					var list = response_data.data;
					$('.btn-add-item').hide();
					$('#id').val(response_data.detail.id);
					$.each(list, function(k,v){
						if(k != 0){
							add_item();
						}
						var f = $('#result2 tbody tr').last();
						f.find('.keb_umum').val(v.id_kebijakan_umum).trigger('change');
						f.find('.perspektif').val(v.id_perspektif).trigger('change');
						f.find('.skala_program').val(v.id_skala_program).trigger('change');
						f.find('.status_program').val(v.status_program).trigger('change');
						f.find('.tujuan').val(v.tujuan);
						f.find('.output').val(v.output);
						f.find('.target_finansial').val(v.target_finansial);
						f.find('.program_kerja').val(v.program_kerja);
						f.find('.dt_id').val(v.id);
						if(v.produk == 1){
							f.find('.produk').prop('checked',true);
						}else{
							f.find('.produk').prop('checked',false);
						}
						if(v.anggaran == 1){
							f.find('.anggaran').prop('checked',true);
						}else{
							f.find('.anggaran').prop('checked',false);
						}

						f.find('.divisi_terkait').val(v.divisi_terkait).trigger('change');
						f.find('.pic').val(v.pic).trigger('change');
						if(response_data.detail_rkf[v.id]){
							$.each(response_data.detail_rkf[v.id],function(kk,vv){
								f.find('.bulan_'+vv.bulan).val(vv.uraian);
								f.find('.bobot_'+vv.bulan).val(vv.bobot);
							});
						}
					})
				}else{
					$('.btn-add-item').show();
				}	
			}
		});
	}
}

$(document).on('click','.btn-add-item',function(){
	add_item();
});
$(document).on('click','.btn-remove',function(){
	$(this).closest('tr').remove();
});
function add_item(p1){
	dt_index += 1;
	var konten = '<tr>';
		konten += '<td><input type="hidden" class="dt_id" name="dt_id[]"/><input type="hidden" class="dt_key" value="'+dt_index+'" name="dt_key[]"/><select class="form-control pilihan keb_umum" name="kebijakan_umum[]" data-validation="required">'+dt_kebijakan_umum+'</select></td>';
		konten += '<td><input type="text" autocomplete="off" class="form-control program_kerja w-200" name="program_kerja[]" aria-label="" data-validation="required"/></td>';
		konten += '<td class="text-center"><div class="custom-checkbox custom-control custom-control-inline"><input class="custom-control-input chk-child produk" id="produk'+dt_index+'" type="checkbox"name="produk'+dt_index+'[]" value="1"> <label class="custom-control-label" for="produk'+dt_index+'">Ya</label></div></td>';
		konten += '<td><select class="form-control pilihan perspektif" name="perspektif[]" data-validation="required">'+dt_perspektif+'</select></td>';
		konten += '<td><select class="form-control pilihan status_program" name="status_program[]" data-validation="required">'+dt_status_program+'</select></td>';
		konten += '<td><select class="form-control pilihan skala_program" name="skala_program[]" data-validation="required">'+dt_skala_program+'</select></td>';
		konten += '<td><input type="text" autocomplete="off" class="form-control tujuan w-200" name="tujuan[]" aria-label="" data-validation="required"/></td>';
		konten += '<td><input type="text" autocomplete="off" class="form-control output w-200" name="output[]" aria-label="" data-validation="required"/></td>';
		konten += '<td><input type="text" autocomplete="off" class="form-control target_finansial w-200" name="target_finansial[]" aria-label="" data-validation="required"/></td>';
		konten += '<td class="text-center"><div class="custom-checkbox custom-control custom-control-inline"><input class="custom-control-input chk-child anggaran" id="anggaran'+dt_index+'" type="checkbox"name="anggaran'+dt_index+'[]" value="1"> <label class="custom-control-label" for="anggaran'+dt_index+'">Ya</label></div></td>';

		var bulan 	= '';
		var uraian  = '';
		var bobot 	= '';
		$.each(arr_bulan,function(k,v){
			bulan += '<input type="text" class="form-control w-200 mb-2" value="'+v+'" disabled/>';
			uraian += '<input type="text" class="form-control w-200 mb-2 bulan_'+k+'" name="bulan_'+dt_index+'_'+k+'[]" autocomplete="off"/>';
			bobot += '<input type="text" class="form-control w-200 mb-2 money text-right bobot_'+k+'" name="bobot_'+dt_index+'_'+k+'[]" autocomplete="off"/>';
		})
		konten += '<td><div class="multiple"><select class="form-control pilihan divisi_terkait" name="divisi_terkait_'+dt_index+'[]"  multiple>'+option_divisi+'</select></div></td>';
		konten += '<td><div class="multiple"><select class="form-control pilihan pic" name="pic'+dt_index+'[]" data-validation="required"  multiple>'+dt_pic+'</select></div></td>';
		konten += '<td>'+bulan+'</td>';
		konten += '<td>'+uraian+'</td>';
		konten += '<td>'+bobot+'</td>';

		konten += '<td><button type="button"class="btn btn-sm btn-icon-only btn-danger btn-remove"><i class="fa-times"></i></button></td>';
	konten += '</tr>';
	$('#result2 tbody').append(konten);
	money_init();
	var $t = $('#result2 .pilihan:last-child');
	$.each($t,function(k,o){
		var $o = $(o);
		$o.select2({
			dropdownParent : $o.parent(),
			placeholder : ''
		});
	})
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
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'');
		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);		
	$.ajax({
		url : base_url + 'transaction/plan_rencana_kerja_fungsi/save_perubahan',
		data 	: {
			'json' : jsonString,
			verifikasi : i
		},
		type : 'post',
		success : function(response) {
			cAlert.open(response,'success','refreshData');
		}
	})
}
$(document).on('click','.btn-detail',function(){
	var page = base_url + 'transaction/plan_rencana_kerja_fungsi/detail/' + $(this).attr('data-id');
	page += '/'+$('#filter_cabang').val();
	$.get(page,function(res){
		cInfo.open(lang.detil,res,{modal_lg:true});
	});
});
$(document).on('click','.btn-act-template',function(){
	var url = base_url + 'transaction/plan_rencana_kerja_fungsi/template';
    $.redirect(url,[],"","_blank");
})
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));

	var data_post = {
		kode_anggaran 	: $('#filter_anggaran option:selected').val(),
		kode_cabang		: $('#filter_cabang option:selected').val(),
		"csrf_token"    : x[0],
	}
	var url = base_url + 'transaction/plan_rencana_kerja_fungsi/export';
    $.redirect(url,data_post,"","_blank");
})
$(document).on('click','.btn-import',function(){
	var kode_anggaran 	= $('#filter_anggaran option:selected').val();
	var kode_cabang		= $('#filter_cabang option:selected').val();
	$('#modal-import').modal('show');
	$('#form-import #kode_anggaran').val(kode_anggaran);
	$('#form-import #kode_cabang').val(kode_cabang);
	$('.fileupload-preview').html('');
    var val = $('#currency option').eq(0).val();
    if(val){
    	$('#currency').val(val).trigger('change');
    }
})
$(document).ready(function(){
	get_currency();
});
function get_currency(){
	$.ajax({
		url : base_url + 'api/currency_option',
		type : 'post',
		data : {},
		dataType : 'json',
		success : function(response) {
			$('#currency').html(response.data);
		}
	});
}

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
    var page = base_url + 'transaction/plan_rencana_kerja_fungsi/keterangan_view';
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
</script>