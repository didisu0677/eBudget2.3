<style type="text/css">
	.bg-c1{
		background-color: #ababab;
	}
	.bg-c2{
		background-color: #d0d0d0;
	}
	.bg-c3{
		background-color: #f5f5f5;
	}
</style>
<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		
		<div class="float-right">
			<label class=""><?php echo lang('anggaran'); ?>  &nbsp</label>

			<select class="select2 infinity custom-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select>					
			               		
			<label class="">&nbsp <?php echo lang('kode_inventaris'); ?>  &nbsp</label>
			<select class="select2 custom-select" id="filter_kode_inventaris">
				<?php foreach ($kode_inventaris as $v) { ?>
                	<option value="<?= $v->kode_inventaris ?>"><?= $v->kode_inventaris.' - '.$v->nama_grup ?></option>
                <?php } ?>
			</select>
			<?php 
				$arr = [];
					$arr = [
					    ['btn-export','Export Data','fa-upload'],
					];
				echo access_button('',$arr); 
			?>
		</div>
		<div class="clearfix"></div>	
	</div>
</div>
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header text-center txt_title"><?= $title ?> <br><span></span></div>
	    			<div class="card-body">
	    				
	    				<?php if($access_edit): ?>
	    				<div class="col-md-4">
	    					<div class="card">
				    			<div class="card-body form-select">
				    			<?php
				    			col_init(2,10);
				    			select2(lang('status'),'id_keterangan_inventaris','required',$keterangan_inventaris,'id','nama');
				    			?>
				    			<div class="form-group row" bis_skin_checked="1">
									<div class="col-sm-9 offset-sm-2" bis_skin_checked="1">
									<button type="button" class="btn btn-info btn-save"><?= lang('simpan'); ?></button>
									</div>
									</div>
				    			</div	>
				    		</div>
	    				</div>
	    				<?php endif; ?>

	    				<div class="table-responsive tab-pane fade active show height-window mt-3" id="result1">
	    				<?php
						table_open('table table-bordered table-app table-1');
							thead('sticky-top');
								tr('d-header');
								tr('d-header');
								tr();
									th(lang('kode_cabang'),'','class="text-center align-middle" style="min-width:80px;width:80px"');
									th(lang('cabang'),'','class="text-center align-middle" style="width:auto;min-width:330px"');
									th(lang('keterangan'),'','class="text-center" style="width:auto;min-width:230px"');
									th(lang('harga'),'','class="text-center" style="width:auto;min-width:100px"');
									th(lang('jumlah'),'','class="text-center" style="width:auto;min-width:50px"');
									for ($i=1; $i <=12 ; $i++) { 
										$column = month_lang($i);
										th($column,'','class="text-center" style="min-width:100px"');
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
$( document ).ready(function() {
	resize_window();
    getData();
});
$('#filter_kode_inventaris').on('change',function(){
	getData();
})
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
    var dt_table = get_data_table('#result1');
    var arr_data = dt_table['arr'];
    var arr_header = dt_table['arr_header'];

    var post_data = {
        "header" 			: JSON.stringify(arr_header),
        "data"        		: JSON.stringify(arr_data),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_inventaris"   : $('#filter_kode_inventaris option:selected').val(),
        "kode_inventaris_txt"   : $('#filter_kode_inventaris option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/rekap_usulan_aset/export';
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
function getData(){
	var txt 	= $('#filter_kode_inventaris').find('option:selected').text();

	var tahun_anggaran = $('#filter_anggaran option:selected').val();
	var kode_inventaris = $('#filter_kode_inventaris').val();
	kode_inventaris = kode_inventaris.replace(" ", "-");

	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/rekap_usulan_aset/data';
	page 	+= '/'+tahun_anggaran;
	page 	+= '/'+kode_inventaris;

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			$('.table-app tbody').html(response.table);
			$('.table-app .d-header').eq(0).html(response.txt_inventaris);
			$('.table-app .d-header').eq(1).html(response.txt_status);
			$(document).find('.txt_title span').html(txt);
			cLoader.close();
			cek_autocode();
			checkSubData();
			var item_act	= {};
			if($('.table-app tbody .btn-input').length > 0) {
				item_act['edit'] 		= {name : lang.realisasi, icon : "edit"};
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

$('.btn-save').on('click',function(){
	var id_status = $('#id_keterangan_inventaris option:selected').val();
	if(!id_status){
		cAlert.open(lang.tidak_ada_data_yang_dipilih);
		return false;
	}
	var msg 	= lang.anda_yakin_menyetujui;
	cConfirm.open(msg,'save_perubahan');
})
function save_perubahan(){
	var id_status = $('#id_keterangan_inventaris option:selected').val();
	if(!id_status){
		cAlert.open(lang.tidak_ada_data_yang_dipilih);
		return false;
	}
	var kode_anggaran = $('#filter_anggaran option:selected').val();
	var kode_inventaris = $('#filter_kode_inventaris').val();
	kode_inventaris = kode_inventaris.replace(" ", "-");

	var data_post = {
		kode_anggaran : kode_anggaran,
		kode_inventaris : kode_inventaris,
		id_status : id_status,
	}

	$.ajax({
		url : base_url + 'transaction/rekap_usulan_aset/save_perubahan',
		data 	: data_post,
		type : 'post',
		success : function(response) {
			cAlert.open(response.message,response.status,response.load);
		}
	})
}
</script>