<style type="text/css">
.style-select2 .select2-container--default{
	width: 100% !important;
}
.style-select2 .select2-container--default .select2-selection--single{
	width: 100% !important;
}
.t-column{
	font-size:10px;
    color:#d80505;
}
.custom-nav li{
	max-width: 100% !important;
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
    		<?php
    			echo $option.' ';
    			if($access_edit):
    				echo '<button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>';
    			endif;
    			$arr = [
				    ['btn-export','Export Data','fa-upload'],
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
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center"><?= $title ?> <br>(<?= get_view_report() ?>)</div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
	    					<?php
							$thn_sebelumnya = user('tahun_anggaran') -1;
							table_open('',false);
								thead('sticky-top');
									tr();
										th(lang('no'),'','width="60" class="text-center align-middle"');
										// th(lang('kebijakan_umum_direksi'),'','style="min-width:250px" rowspan="2" class="text-center align-middle"');
										th(lang('program_kerja'),'','class="text-center align-middle" style="min-width:250px"');
										th(lang('nama_akun'),'','style="min-width:200px" class="text-center align-middle"');
										th(lang('keterangan'),'','style="min-width:200px" class="text-center align-middle"');
										th(lang('pd_bulan'),'','style="min-width:100px" class="text-center align-middle"');
										foreach ($detail_tahun as $k2 => $v2) {
											$column = month_lang($v2->bulan).' '.$v2->tahun;
											$column .= '<br>('.$v2->singkatan.')';
											$column .= '<br> <span class="t-column">Biaya Pd Bulan</span>';
											th($column,'','style="min-width:100px" class="text-center align-middle"');
										}
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
modal_open('modal-form','','modal-lg',' data-openCallback="formOpen"');
	modal_body();
		form_open(base_url('transaction/plan_proker/save'),'post','form'); 
			col_init(2,4);
				input('hidden','id','id');
				input('text',lang('tahun'),'tahun_anggaran','',user('tahun_anggaran'),'disabled');
				echo cabang($cabang_input);
			col_init(2,9);
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();

function cabang($cabang_input){
	$option = '';
	foreach($cabang_input as $b){
	if($b['kode_cabang'] == user('kode_cabang'))  $selected = ' selected'; else $selected = '';
	$option .= '<option value="'.$b['kode_cabang'].'"'.$selected.'>'.$b['nama_cabang'].'</option>';
	$item = '<div class="form-group row">
		<label class="col-form-label col-md-2">'.lang('cabang').' &nbsp</label>
		<div class="col-md-4 col-9 mb-1 mb-md-0">	
			<select class="select2 infinity custom-select" id="kode_cabang" name="kode_cabang">'.$option.'</select>   
		</div>
	</div>';
	$item .= '<div class="card mb-2">
				<div id="result2" class="mb-3">	
				<div class="table-responsive">
				    <table class="table table-bordered" id="result2">
						<thead class="sticky-top">
							<tr>
								<th class="text-center">Akun</th>
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
	}
	return $item;
}
?>
<script type="text/javascript">
var dt_index = 0;
var response_data = [];
$(document).ready(function () {
	getData();
});
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){getData();});
function getData() {
	var cabang = $('#filter_cabang').val();
	if(!cabang){ return ''; }
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/plan_proker/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
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
			cLoader.close();
			var item_act	= {};
			if($('.table-app tbody .btn-input').length > 0) {
				item_act['edit'] 		= {name : lang.ubah, icon : "edit"};
			}
			if($('.table-app tbody .item-coa').length>0){
				$('.item-coa').select2();
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
var field_not_number = ['keterangan'];
$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
	var val = $(this).text();
	var data_name = $(this).attr('data-name');
	if(!inArray(data_name,field_not_number)){
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
	var data_name = $(this).attr('data-name');
	if(!inArray(data_name,field_not_number)){
		var val = $(this).text();
		var minus = val.includes("-");
		if(minus){
			val = val.replace('-','');
			$(this).text('('+val+')');
		}
	}
});
$(document).on('keyup','.edit-value',function(e){
	var n = $(this).text();
	var data_name = $(this).attr('data-name');
	if(!inArray(data_name,field_not_number)){
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
	i += $(document).find('.edited-select').length;
	if(i == 0) {
		cAlert.open('tidak ada data yang di ubah');
	} else {
		var msg 	= lang.anda_yakin_menyetujui;
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
		var data_name = $(this).attr('data-name');
		if(!inArray(data_name,field_not_number)){
			data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'');
		}else{
			data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		}
		
		i++;
	});

	$(document).find('.edited-select').each(function(){
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		var val = $(this).find('option:selected').val();
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = val;
	});
	
	var jsonString = JSON.stringify(data_edit);		
	$.ajax({
		url : base_url + 'transaction/plan_proker/save_perubahan',
		data 	: {
			'json' : jsonString,
			verifikasi : i,
			'kode_anggaran' : $('#filter_anggaran option:selected').val(),
			'kode_cabang' : $('#filter_cabang option:selected').val(),
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
$(document).on('change','.item-coa',function(){
	dt = $(this).data();
	var val = $(this).val();
	if(val == dt.selected){
		$(this).removeClass('edited-select');
	}else{
		$(this).addClass('edited-select');
	}
})
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

    var post_data = {
        "data"        		: JSON.stringify(dt),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/plan_proker/export';
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
</script>