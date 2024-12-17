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
    			echo ' ';
    			if($access_edit):
    				echo '<button class="btn btn-success btn-save" href="javascript:;" > '.lang('simpan').' <span class="fa-save"></span></button>';
    			endif;
    			if($access_edit && $access_additional):
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
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
						<?php 
							table_open('table table-striped table-bordered table-app table-1 table-hover',false);
							thead('sticky-top');
								tr();
									th(get_view_report(),'','colspan="'.(4+count($detail_tahun)).'"');
								tr();
									th(lang('sandi bi'),'','width="60" rowspan="2" class="text-center align-middle"');
									th(lang('coa').' 5','','width="60" rowspan="2" class="text-center align-middle"');
									th(lang('coa 7'),'','width="60" rowspan="2" class="text-center align-middle"');
									th(lang('keterangan'),'','rowspan="2" class="text-center align-middle" style="width:auto;min-width:230px;"');

									for ($i = 1; $i <= 12; $i++) {
										$a = array_search($i, array_column($detail_tahun, 'bulan'));
										$column = month_lang($i);
										$column .= "<br> (".$detail_tahun[$a]['singkatan'].")";
										th($column,'','class="text-center" style="min-width:80px;"');
									}

									// for ($i = 1; $i <= 12; $i++) { 
									// 	th(month_lang($i),'','class="text-center" style="min-width:80px;background-color: #e64a19; color: white !important;"');		
									// }

									th('','','class="border-none bg-transparent" style="min-width:80px;"');
									th('');
									$column = $bulan_terakhir.' '.$tahun->tahun_terakhir_realisasi;
									$column .= '<br> ('.arrSumberData()['real'].')';
									th($column,'','class="text-center" style="min-width:150px"');
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
modal_open('modal-info','Peringatan');
	modal_body();
		echo 'Terdapat Nilai yang lebih kecil dari bulan sebelumnya. Silahkan dicek kembali.';
modal_close();
?>
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
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){
		return false;
	}
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/laba_rugi_new/data/';
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
            	cLoader.close();
            	$('#result1 tbody').html('');
            	cAlert.open(res.message,'failed');
            	return false;
            }else{
            	$('#result1 tbody').html(res.table);
	            checkSubData();
	            if(res.access_edit){
	                $('.btn-save').prop('disabled',false);
	                $('.btn-save').show();
	            }else{
	                $('.btn-save').prop('disabled',true);
	                $('.btn-save').hide();
	            }
				kurangSelisih();
				cLoader.close();
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

$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
	var val = $(this).text();
	var minus = val.includes("(");
	if(minus){
		val = val.replace('(','');
		val = val.replace(')','');
		$(this).text('-'+val);
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
	var val = $(this).text();
	var minus = val.includes("-");
	if(minus){
		val = val.replace('-','');
		$(this).text('('+val+')');
	}
});

$(document).on('blur','.edit-bulan',function(){
	var tr = $(this).closest('tr');
	if($(this).text() != $(this).attr('data-value')) {
		$(this).addClass('editedBulan');
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

$(document).on('click','.btn-remove',function(){
	var dt_id = $(this).attr('data-id');
	if(dt_id){
		del_id 		= $('#filter_cabang option:selected').val();
		urlDelete 	= base_url+"transaction/laba_rugi_new/delete_adj";
		cConfirm.open(lang.anda_yakin_menghapus_data_ini + '?','deleteData');
	}
})

$(document).on('click','.btn-adj',function(){
	var i = 0;
	$('.sdbulan').each(function(){
		i++;
	});
	if(i == 0) {
		cAlert.open('tidak ada data yang di ubah');
	} else {
		var msg 	= lang.anda_yakin_menyetujui;
		if( i == 0) msg = lang.anda_yakin_menolak;
		cConfirm.open(msg,'save_adj');        
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
	// $(this)[0].focus();
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
// $(document).on('keypress','.edit-value',function(e){
// 	var wh 			= e.which;
// 	if (e.shiftKey) {
// 		if(wh == 0) return true;
// 	}
// 	if(e.metaKey || e.ctrlKey) {
// 		if(wh == 86 || wh == 118) {
// 			$(this)[0].onchange = function(){
// 				$(this)[0].innerHTML = $(this)[0].innerHTML.replace(/[^0-9\-]/g, '');
// 			}
// 		}
// 		return true;
// 	}
// 	if(wh == 0 || wh == 8 || wh == 45 || (48 <= wh && wh <= 57) || (96 <= wh && wh <= 105)) 
// 		return true;
// 	return false;
// });

function kurangSelisih(){
	var b;
    for(var i=1;i<=12;i++){
	    var data 	= $("#input"+i).text();
	    var laba 	= $("#labarugi_"+i).text();

	    var minus = data.includes("(");
		if(minus){
			data = data.replace('(','');
			data = data.replace(')','');
			data = '-'+data;
		}
		var datax  	= formatCurrency(data,'',2);

		var minus = laba.includes("(");
		if(minus){
			laba = laba.replace('(','');
			laba = laba.replace(')','');
			laba = '-'+laba;
		}
		var labax 	= formatCurrency(laba,'',2);

	    data = '';
	    laba = '';

	    $.each(datax.split('.'),function(k,v){
	    	data += ''+v;
	    });
	    $.each(labax.split('.'),function(k,v){
	    	laba += ''+v;
	    });
	    
        if(data != ''){
            b  = parseInt(b) + parseInt(data);
        }else {
            b = b;
        }

        if(i == 1){
            b = data;
        }
	    
	    var c = parseInt(b);
	    var d = parseInt(laba) - parseInt(b);

	    // console.log('laba '+ laba+' || data inpu '+b+' || hasil '+d);
	    if(i == 12){
	    	var hasil12 = $('#hasil12').text();
			var minus = hasil12.includes("(");
			if(minus){
				hasil12 = hasil12.replace('(','');
				hasil12 = hasil12.replace(')','');
				hasil12 = '-'+hasil12;
			}
			hasil12 = moneyToNumber(hasil12)

			var hasil11 = $('#hasil11').text();
			var minus = hasil11.includes("(");
			if(minus){
				hasil11 = hasil11.replace('(','');
				hasil11 = hasil11.replace(')','');
				hasil11 = '-'+hasil11;
			}
			hasil11 = moneyToNumber(hasil11);
			$('#input12').text(customFormat((hasil12-hasil11),0));
			var hasil_laba = parseInt(laba) - hasil12;
			$('#selisih12').text(customFormat(hasil_laba,0));
	    }else{
	    	var e = parseInt(d);
		    $("#hasil"+i).text(numberFormat(c,0,',','.'));
		    $("#selisih"+i).text(numberFormat(e,0,',','.'));
	    }
	}
	
}

$(document).on('keyup','.cuan',function(e){
	var b;
    for(var i=1;i<=12;i++){
	    var data 	= $("#input"+i).text();
	    var laba 	= $("#labarugi_"+i).text();

	    var minus = data.includes("(");
		if(minus){
			data = data.replace('(','');
			data = data.replace(')','');
			data = '-'+data;
		}
		var datax  	= formatCurrency(data,'',2);

		var minus = laba.includes("(");
		if(minus){
			laba = laba.replace('(','');
			laba = laba.replace(')','');
			laba = '-'+laba;
		}
		var labax 	= formatCurrency(laba,'',2);

	    data = '';
	    laba = '';

	    $.each(datax.split('.'),function(k,v){
	    	data += ''+v;
	    });
	    $.each(labax.split('.'),function(k,v){
	    	laba += ''+v;
	    });
	    
        if(data != ''){
            b  = parseInt(b) + parseInt(data);
        }else {
            b = b;
        }

        if(i == 1){
            b = data;
        }
	    
	    var c = parseInt(b)
	    var d = parseInt(laba) - parseInt(b);

	    // console.log('laba '+ laba+' || data inpu '+b+' || hasil '+d);
	    if(i == 12){
	    	var hasil12 = $('#hasil12').text();
			var minus = hasil12.includes("(");
			if(minus){
				hasil12 = hasil12.replace('(','');
				hasil12 = hasil12.replace(')','');
				hasil12 = '-'+hasil12;
			}
			hasil12 = moneyToNumber(hasil12)

			var hasil11 = $('#hasil11').text();
			var minus = hasil11.includes("(");
			if(minus){
				hasil11 = hasil11.replace('(','');
				hasil11 = hasil11.replace(')','');
				hasil11 = '-'+hasil11;
			}
			hasil11 = moneyToNumber(hasil11);
			$('#input12').text(customFormat((hasil12-hasil11),0));
			var hasil_laba = parseInt(laba) - hasil12;
			$('#selisih12').text(customFormat(hasil_laba,0));
	    }else{
	    	var e = parseInt(d)
		    $("#hasil"+i).text(numberFormat(c,0,',','.'));
		    $("#selisih"+i).text(numberFormat(e,0,',','.'));
	    }
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

	// var tahun_anggaran = $('#filter_anggaran option:selected').val();
	// var coa = $('#filter_coa').val();
	var page = base_url + 'transaction/laba_rugi_new/save_perubahan';
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
	var page = base_url + 'transaction/laba_rugi_new/save_nett';
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


function save_adj() {
	var data_edit = {};
	var i = 0;
	
	$('.adj').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		i++;
	});

	$('.pdbulan').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		i++;
	});


	$('.sdbulan').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);	
	// var tahun_anggaran = $('#filter_anggaran option:selected').val();
	// var coa = $('#filter_coa').val();
	var page = base_url + 'transaction/laba_rugi_new/save_adj';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
    page += '/'+ $('#ke').val();
    page += '/'+ $('#di').val();
	$.ajax({
		url : page,
		data 	: {
			'json' : jsonString,
			verifikasi : i
		},
		type : 'post',
		success : function(response) {
			if(response.status == 'failed'){
				cAlert.open(response.message,'failed');
				return false;
			}
			cAlert.open(response.message,'success',response.load);
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
    var url = base_url + 'transaction/laba_rugi_new/export';
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
$(document).on('click','.btn-warning',function(){
	cAlert.open('Terdapat Nilai yang lebih kecil dari bulan sebelumnya. Silahkan dicek kembali.','warning');
});
$(document).on('click','.btn-info-coa',function(){
	cAlert.open('Terdapat Aksi edit dan aksi selisih pada coa yang sama. silahkan cek kembali.','warning');
});
</script>