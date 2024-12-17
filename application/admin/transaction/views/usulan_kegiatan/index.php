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
			<?= filter_cabang_admin($access_additional,$cabang); ?>
			<button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>	
    		
    		<?php 
				$arr = [];
					$arr = [
						// ['btn-save','Save Data','fa-save'],
					    ['btn-export','Export Data','fa-upload'],
					    // ['btn-import','Import Data','fa-download'],
					    // ['btn-template','Template Import','fa-reg-file-alt']
					];
				
				
				echo access_button('',$arr); 
			?>
    		</div>
			<div class="clearfix"></div>
			
		</div>
	</div>

<div class="content-body mt-6">
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header text-center"><?= $title ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
	    				<?php
						$thn_sebelumnya = user('tahun_anggaran') -1;
						table_open('',false,'','','data-table="tbl_m_produk"');
							thead();
								tr();
									th(get_view_report(),'','colspan="15" class="text-left"');	
								tr();	
									th(lang('no'),'','width="60" rowspan="2" class="text-center align-middle"');
									th(lang('nama_kegiatan'),'','width="250" rowspan="2" class="text-center align-middle"');
									
									for ($a=1;$a<=12;$a++) {
										th(month_lang($a) . ' '. $tahun->tahun_anggaran,'','class="text-center"');		
									}

									th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
								tr();
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
									th('PD.BULAN','','class="text-center"');
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
if($access_additional):
	$cabang_input = $cabang;
endif;
modal_open('modal-form','','modal-lg','data-openCallback="formOpen"');
	modal_body();
		form_open(base_url('transaction/usulan_kegiatan/save'),'post','form'); 
				col_init(2,4);
				input('hidden','id','id');

			input('text',lang('tahun'),'tahun_anggaran','',user('tahun_anggaran'),'disabled');
				col_init(2,9);
			?>
	

			<div class="form-group row">
				<label class="col-form-label col-md-2"><?php echo lang('cabang'); ?>  &nbsp</label>
				<div class="col-md-4 col-9 mb-1 mb-md-0">	
					<select class="select2 infinity custom-select" id="kode_cabang" name="kode_cabang">
		                
					</select>   
				</div>
			</div>

			<div class="card mb-2">
				<div class="card-header"><?php echo lang('nama_kegiatan'); ?></div>
				<div class="card-body">
		            <div class="form-group row">
						<div class="col-md-11 col-12 mb-1 mb-md-0">
							<input type="hidden" class="dt_id" id="dt_id" name="dt_id[]">
							<input type="text" name="keterangan[]" autocomplete="off" class="form-control keterangan" data-validation="required|max-length:255" placeholder="<?php echo lang('keterangan'); ?>" aria-label="<?php echo lang('keterangan'); ?>" id="keterangan">
						</div>

						<div class="col-md-1 col-3 mb-1 mb-md-0">
							<button type="button" class="btn btn-block btn-success btn-icon-only btn-add-anggota"><i class="fa-plus"></i></button>
						</div>
					</div>	
					<div id="additional-anggota" class="mb-2"></div>
				</div>	
			</div>		

	<?php

				form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close(); ?>

?>

<script type="text/javascript">

$('#filter_tahun').change(function(){
	resize_window();
	getData();
});

$('#filter_cabang').change(function(){
	getData();
});

$(document).ready(function () {

	getData();
	select_value = $('#grup_aset').html();
	select_kel1 = $('#inv_kel1').html();
	select_kel2 = $('#inv_kel2').html();
    $(document).on('keyup', '.calculate', function (e) {
        calculate();
    });
});	

$('#filter_tahun').change(function(){
	getData();
});

function getData() {
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){
		return false;
	}
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/usulan_kegiatan/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			if(!response.status){
				cLoader.close();
				$('.table-app tbody').html('');
				cAlert.open(response.message,'failed');
				return false;
			}
			$('.table-app tbody').html(response.table);
			$('#parent_id').html(response.option);
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


			if(!response.edit) {	
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
$(function(){
	getData();
});

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
	var val = $(this).text();
	var minus = val.includes("(");
	if(minus){
		val = val.replace('(','');
		val = val.replace(')','');
		$(this).text('-'+val);
	}
	console.log(minus); 
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
	
	var anggaran = $('#filter_anggaran option:selected').val();
	var cabang 	 = $('#filter_cabang option:selected').val();
	var jsonString = JSON.stringify(data_edit);		
	$.ajax({
		url : base_url + 'transaction/usulan_kegiatan/save_perubahan',
		data 	: {
			'json' : jsonString,
			'kode_anggaran' : anggaran,
			'kode_cabang' : cabang,
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


$('.btn-import').click(function(){
	$('#form-import')[0].reset();
	$('#tahun').val($('#filter_tahun').val()).trigger("change")
	$('#kode_harga').val($('#filter_harga').val()).trigger("change");
	$('#bisunit').val($('#filter_divisi').val()).trigger("change");

    $('#modal-import .alert').hide();
    $('#modal-import').modal('show');

});


$(document).on('click','.btn-export',function(){
	var currentdate = new Date(); 
	var datetime = currentdate.getDate() + "/"
	                + (currentdate.getMonth()+1)  + "/" 
	                + currentdate.getFullYear() + " @ "  
	                + currentdate.getHours() + ":"  
	                + currentdate.getMinutes() + ":" 
	                + currentdate.getSeconds();
	
	$('.bg-grey-2').each(function(){
		$(this).attr('bgcolor','#f4f4f4');
	});
	$('.bg-grey-2').each(function(){
		$(this).attr('bgcolor','#dddddd');
	});
	$('.bg-grey-2-1').each(function(){
		$(this).attr('bgcolor','#b4b4b4');
	});
	$('.bg-grey-2-2').each(function(){
		$(this).attr('bgcolor','#aaaaaa');
	});
	$('.bg-grey-2').each(function(){
		$(this).attr('bgcolor','#888888');
	});
	var table	= '<table>';
	table += '<tr><td colspan="1">Bank Jateng</td></tr>';
	table += '<tr><td colspan="1"> Usulan PD.BULAN Besaran Tertentu </td><td colspan="25">: '+$('#filter_tahun option:selected').text()+'</td></tr>';
	table += '<tr><td colspan="1"> Cabang </td><td colspan="25">: '+$('#filter_cabang option:selected').text()+'</td></tr>';
	table += '<tr><td colspan="1"> Print date </td><td colspan="25">: '+datetime+'</td></tr>';
	table += '</table><br />';
	table += '<table border="1">';
	table += $('.content-body').html();
	table += '</table>';
	var target = table;
	window.open('data:application/vnd.ms-excel,' + encodeURIComponent(target));
	$('.bg-grey-1,.bg-grey-2.bg-grey-2-1,.bg-grey-2-2,.bg-grey-3').each(function(){
		$(this).removeAttr('bgcolor');
	});
});

$(document).on('click','.btn-template',function(){
	var page = base_url + 'pl_sales/target_produk/template'
	   $.ajax({
		      url:page,
		      complete: function (response) {
		    	  window.open(page);
		      },
		  });
});

$('.btn-add-anggota').click(function(){
	add_row_anggota();
});
$(document).on('click','.btn-remove-anggota',function(){
	$(this).closest('.form-group').remove();
});

function add_row_anggota() {
	konten = '<div class="form-group row">'
			+ '<div class="col-md-11 col-12 mb-1 mb-md-0">'
			+ '<input type="hidden" class="dt_id" name="dt_id[]">'
			+ '<input type="text" name="keterangan[]" autocomplete="off" class="form-control keterangan" data-validation="required|max-length:255" placeholder="'+$('#keterangan').attr('placeholder')+'" aria-label="'+$('#keterangan').attr('placeholder')+'">'
			+ '</div>'
			+ '<div class="col-md-1 col-3 mb-1 mb-md-0">'
			+ '<button type="button" class="btn btn-block btn-danger btn-icon-only btn-remove-anggota"><i class="fa-times"></i></button>'
			+ '</div>'
			+ '</div>'
			$('#additional-anggota').append(konten);
			var $t = $('#additional-anggota .grup_aset:last-child');
			$t.select2({
				dropdownParent : $t.parent()
			});
}

function formOpen() {
	$('#additional-anggota').html('');
	$('#additional-anggota').html('');
	var cabang = $('#filter_cabang option:selected').val();
	var cabang_txt = $('#filter_cabang option:selected').text();
	$('#kode_cabang').html('<option value="'+cabang+'">'+cabang_txt+'</option>');
	var response = response_edit;
	if(typeof response.id != 'undefined') {
		$('.btn-add-anggota').hide();
		$('.btn-remove-anggota').show();
		$.each(response.detail_ket,function(e,d){
			if(e == '0') {
				$('#keterangan').val(d.nama_kegiatan);	
				$('#dt_id').val(d.id);	
			} else {
				add_row_anggota();
				$('#additional-anggota .dt_id').last().val(d.id);
				$('#additional-anggota .keterangan').last().val(d.nama_kegiatan);
		
			}
		});

	}else {
		$('#dt_id').val('');
		$('.btn-add-anggota').show();
		$('.btn-remove-anggota').hide();
	}
}
var temp_del_id = '';
$(document).on('click','.btn-del',function(){
	temp_del_id = $(this).attr('data-id');
	if(!temp_del_id){
		cAlert.open(lang.tidak_ada_data_yang_dipilih);
	}
	var msg 	= lang.anda_yakin_menghapus_data_ini;
	cConfirm.open(msg+'?','delete_perubahan');

})
function delete_perubahan(){
	$.ajax({
		url : base_url + 'transaction/usulan_kegiatan/delete_perubahan',
		data 	: {
			id : temp_del_id,
			kode_anggaran : $('#filter_anggaran option:selected').val(),
			kode_cabang : $('#filter_cabang option:selected').val(),
		},
		type : 'post',
		success : function(response) {
			cAlert.open(response.message,response.status,response.load);
		}
	})
}
</script>