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
			<select class="select2 infinity number-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select>

    		<?php
    			echo filter_cabang_admin($access_additional,$cabang,['kanpus' => 1]);
    			echo ' ';
				if($akses_ubah == 1):
    				echo '<button class="btn btn-success btn-save" href="javascript:;" > '.lang('simpan').' <span class="fa-save"></span></button>';
    			endif;
    			if($akses_ubah && $access_additional):
    				echo '<button class="btn btn-success btn-save-nett" href="javascript:;" > '.lang('simpan_ke_budget_nett').' <span class="fa-save"></span></button>';
    			endif;
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
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1" data-height="10">
						<?php 
						table_open('table table-striped table-bordered table-app table-hover',false);
							thead('sticky-top');
								tr();
									th(get_view_report(),'','colspan="'.(4+count($detail_tahun)).'"');
								tr();
									th(lang('sandi bi'),'','width="60" rowspan="2" class="text-center align-middle headcol"');
									th(lang('coa 5'),'','width="60" rowspan="2" class="text-center align-middle headcol"');
									th(lang('coa 7'),'','width="60" rowspan="2" class="text-center align-middle headcol"');
									th(lang('keterangan'),'','rowspan="2" class="text-center align-middle headcol" style="min-width:230px"');

									foreach ($detail_tahun as $k => $v) {
										$column = month_lang($v->bulan).' '.$v->tahun;
										$column .= '<br> ('.$v->singkatan.')';
										th($column,'','class="text-center" style="min-width:100px"');
									}
									th('','','class="border-none bg-transparent" style="min-width:80px;"');
									$column = $bulan_terakhir.' '.$tahun->tahun_terakhir_realisasi;
									$column .= '<br> ('.arrSumberData()['real'].')';
									th($column,'','class="text-center" style="min-width:100px"');
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
var xhr_ajax = null;
$(document).ready(function(){
	resize_window();
	var cabang = $('#filter_cabang').val();
	if(cabang){
		loadData();
	}
});	

$('#filter_anggaran').change(function(){
	var cabang = $('#filter_cabang').val();
	if(cabang){
		loadData();
	}
});

$('#filter_cabang').change(function(){
	var cabang = $('#filter_cabang').val();
	if(cabang){
		loadData();
	}
});
function loadData(){
	$('#result1 tbody').html('');	
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/neraca_new/data/';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	if(!res.status){
        		$('#result1 tbody').html('');
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$('#result1 tbody').append(res.view);
        	if(res.status){
        		loadMore(res.count);
        	}else{
        		cLoader.close();
        	}
		}
    });
}
function loadMore(count){
	var page = base_url + 'transaction/neraca_new/loadMore';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
    page += '/'+ count;
    $.ajax({
        url: page,
        type: 'post',
		data : {count:count},
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	$('#result1 tbody').append(res.view);
        	if(res.status){
        		loadMore(res.count);
        	}else{
        		checkAdjusment();
        	}

        	if(res.access_edit){
                $('.btn-save').prop('disabled',false);
                $('.btn-save').show();
            }else{
                $('.btn-save').prop('disabled',true);
                $('.btn-save').hide();
            }
		}
    });
}
function checkAdjusment(){
	var page = base_url + 'transaction/neraca_new/checkAdjusment';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
    $.ajax({
        url: page,
        type: 'post',
		data : {},
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	$.each(res,function(k,v){
        		if(k == 'd-total-pasiva' || k == 'd-spaces'){
        			$('.'+k).html(v);
        		}else if(k == 'append_table'){
        			$('#result1 tbody').append(v);
        		}else{
        			$.each(v,function(k2,v2){
	        			$('.d-'+k+' .'+k2).html(v2);
	        		});
        		}
        	});
        	cLoader.close();
    		checkSubData();
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
	$.ajax({
		url : base_url + 'transaction/neraca_new/save_perubahan',
		data 	: {
			'json' : jsonString,
			verifikasi : i,
			'kode_anggaran' : $('#filter_anggaran option:selected').val(),
		},
		type : 'post',
		success : function(response) {
			if(response.status == 'failed'){
	        	cAlert.open(response.message,'failed');
	        	return false;
	        }
			cAlert.open(response.message,'success','loadData');
		}
	})
}
var status_nett;
$('.btn-save-nett').on('click',function(){
	status_nett = true;
	save_nett();
})
function save_nett(){
	var page = base_url + 'transaction/neraca_new/save_nett';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
    if(!status_nett){
    	page += '/'+ status_nett;
    }
    if(status_nett){
    	cLoader.open(lang.memuat_data + '...');
    }
	$.ajax({
		url : page,
		type : 'post',
		success : function(response) {
			if(response.status == 'failed'){
				cAlert.open(response.message,'failed');
				return false;
			}
			if(!status_nett){
				cConfirm.close();
				cInfo.open(response.message,response.view);
			}else{
				cLoader.close();
				status_nett = false;
				var msg = response.message;
				msg += '\n'+lang.anda_yakin_menyetujui.replace(/[^a-z A-Z\-]/g, '')+' '+$('.btn-save-nett').text().toLowerCase()+'?';
				cConfirm.open(msg,'save_nett');
			}
		}
	})
}
$(document).on('click','.btn-export',function(){
    var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
    var dt_neraca = get_data_table('#result1');
    var arr_neraca = dt_neraca['arr'];
    var arr_neraca_header = dt_neraca['arr_header'];

    var post_data = {
        "neraca_header" : JSON.stringify(arr_neraca_header),
        "neraca"        : JSON.stringify(arr_neraca),
        "kode_anggaran" : $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   : $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "csrf_token"    : x[0],
    }
    var url = base_url + 'transaction/neraca_new/export';
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
            if(no == 0){
                arrayOfThisRowHeader.push("");
                arrayOfThisRowHeader.push("");
                arrayOfThisRowHeader.push("");
                arrayOfThisRowHeader.push("");
                tableDataHeader.each(function(k,v) {
                    var val = $(this).text();
                    if(val && val != '-'){
                        if(index_cabang != 0){
                            arrayOfThisRowHeader.push("");
                        }
                        index_cabang++;
                        arrayOfThisRowHeader.push($(this).text());
                        for (var i = 1; i <= 11; i++) {
                            arrayOfThisRowHeader.push("");
                        }
                    }
                });
                arr_header.push(arrayOfThisRowHeader);
            }

            if(no == 1){
                tableDataHeader.each(function(k,v) {
                    var val = $(this).text();
                    arrayOfThisRowHeader.push($(this).text());
                });
                arr_header.push(arrayOfThisRowHeader);

                arr.push(arrayOfThisRowHeader);
            }
            no++; 
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