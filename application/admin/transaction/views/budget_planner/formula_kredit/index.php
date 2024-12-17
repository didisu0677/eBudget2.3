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
    		    echo ' <button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>';
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
	    				<div class="table-responsive tab-pane fade active show">
							<?php
							table_open('table table-bordered table-app table-1');
								thead('sticky-top');
									tr();
										th('Coa','','class="text-center align-middle" style="width:auto;min-width:60px"');
										th('keterangan','','class="text-center align-middle" style="width:auto;min-width:330px"');

										for ($i = $anggaran['bulan_terakhir_realisasi'] -1; $i <= $anggaran['bulan_terakhir_realisasi']; $i++) { 
											$x = 'Real';
											$column = month_lang($i).' '.$anggaran['tahun_terakhir_realisasi'];
											$column .= '<br> ('.$x.')';
											th($column,'','class="text-center" style="min-width:100px"');					
										}
												
										foreach ($detail_tahun as $v) {
											if($v->singkatan != arrSumberData()['real']):
												$column = month_lang($v->bulan).' '.$v->tahun;
												$column .= '<br> ('.$v->singkatan.')';
												th($column,'','class="text-center" style="min-width:100px"');
											endif;
										};
								
								tbody();
							table_close();
							?>
						</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>

	<div class="main-container">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
	    					<?php
							table_open('table table-bordered table-app table-2');
								thead('sticky-top');
									tr();
										th('Coa','','class="text-center align-middle" style="width:auto;min-width:60px"');
										th('keterangan','','class="text-center align-middle" style="width:auto;min-width:330px"');

										for ($i = $anggaran['bulan_terakhir_realisasi'] -1; $i <= $anggaran['bulan_terakhir_realisasi']; $i++) { 
											$x = 'Real';
											$column = month_lang($i).' '.$anggaran['tahun_terakhir_realisasi'];
											$column .= '<br> ('.$x.')';
											th($column,'','class="text-center" style="min-width:100px"');					
										}

										foreach ($detail_tahun as $v) {
											if($v->singkatan != arrSumberData()['real']):
												$column = month_lang($v->bulan).' '.$v->tahun;
												$column .= '<br> ('.$v->singkatan.')';
												th($column,'','class="text-center" style="min-width:100px"');
											endif;
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
<script type="text/javascript">
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
</script>