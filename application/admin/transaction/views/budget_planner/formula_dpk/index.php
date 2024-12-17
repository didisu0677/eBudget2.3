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
				$arr = [
				    ['btn-export','Export Data','fa-upload'],
				];
				echo access_button('',$arr); 
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
	    			<div class="card-header"><?= 'Giro' ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
						<?php
						table_open('table table-bordered table-app table-1');
							thead('sticky-top');
								tr();
									th(get_view_report(1),'','width="60" colspan="'.(count($detail_tahun)+4).'" class="text-left"');
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

	    	<div class="col-sm-12 mt-3">
				<div class="card">
	    			<div class="card-header"><?= 'Tabungan' ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="res_tab">
						<?php
						table_open('table table-bordered table-app table-1');
							thead('sticky-top');
								tr();
									th(get_view_report(1),'','width="60" colspan="'.(count($detail_tahun)+4).'" class="text-left"');
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

	    	<div class="col-sm-12 mt-3">
				<div class="card">
	    			<div class="card-header"><?= 'Simpanan Berjangka' ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="res_deposito">
						<?php
						table_open('table table-bordered table-app table-1');
							thead('sticky-top');
								tr();
									th(get_view_report(1),'','width="60" colspan="'.(count($detail_tahun)+4).'" class="text-left"');
								tr();
									th('Coa','','class="text-center align-middle" style="width:auto;min-width:60px"');
									th('keterangan','','class="text-center align-middle" style="width:auto;min-width:330px"');
									th('Rate','','class="text-center align-middle" style="width:auto;min-width:60px"');

									foreach ($detail_tahun as $v) {
										$column = month_lang($v->bulan).' '.$v->tahun;
										$column .= '<br> ('.$v->singkatan.')';
										th($column,'','class="text-center" style="min-width:100px"');
									}
							
							tbody();
						table_close();
						?>
						</div>

						<div class="table-responsive tab-pane fade active show height-window mt-3 " id="res_deposito2">
						<?php
						table_open('table table-bordered table-app table-1');
							thead('sticky-top');
								tr();
									th(get_view_report(1),'','width="60" colspan="'.(count($detail_tahun)+4).'" class="text-left"');
								tr();
									th('Coa','','class="text-center align-middle" style="width:auto;min-width:60px"');
									th('keterangan','','class="text-center align-middle" style="width:auto;min-width:330px"');

									if(count($arr_tahun)>1):
										for ($i = $anggaran['bulan_terakhir_realisasi'] -1; $i <= $anggaran['bulan_terakhir_realisasi']; $i++) { 
											$x = 'Real';
											$column = month_lang($i).' '.$anggaran['tahun_terakhir_realisasi'];
											$column .= '<br> ('.$x.')';
											th($column,'','class="text-center" style="min-width:100px"');					
										}
									endif;

									foreach ($detail_tahun as $v) {
										if($v->singkatan != arrSumberData()['real'] || count($arr_tahun) == 1):
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

</div>
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

var xhr_ajax = null;
function loadData(){
	$('#result1 tbody').html('');	
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/formula_dpk/data/';
        page += '/'+ $('#filter_anggaran').val();
    	page += '/'+ $('#filter_cabang').val();
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
            $('#result1 tbody').html(res.table);	
            $('#res_tab tbody').html(res.tab);	
            $('#res_deposito tbody').html(res.deposito);	
            $('#res_deposito2 tbody').html(res.deposito2);
            if(res.access_edit){
                $('.btn-save').prop('disabled',false);
                $('.btn-save').show();
            }else{
                $('.btn-save').prop('disabled',true);
                $('.btn-save').hide();
            }
            cLoader.close();
		}
    });
}

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
	var page = base_url + 'transaction/formula_dpk/save_perubahan';
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
		urlDelete 	= base_url+"transaction/formula_dpk/delete";
		urlDelete += '/'+$('#filter_anggaran option:selected').val();
		urlDelete += '/'+$('#filter_cabang option:selected').val();
		cConfirm.open(lang.anda_yakin_menghapus_data_ini + '?','deleteData');
	}
})
</script>