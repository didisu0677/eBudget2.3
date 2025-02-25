<style type="text/css">
	.wd-100{
		width: 100px !important;
		min-width: 100px !important;
		max-width: 100px !important;
	}
	.wd-150{
		width: 150px !important;
		min-width: 150px !important;
		max-width: 150px !important;
	}
	.wd-230{
		width: 350px !important;
		min-width: 350px !important;
		max-width: 350px !important;
	}
	.d-bg-header th{
		background-color: #e64a19 !important;
	}
	.d-bg-header span{
		color: #fff !important;
	}
	.d-bg-header red{
		color: #e64a19 !important;
	}
</style>
<div class="content-header">
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

			<label class=""><?php echo lang('cabang'); ?>  &nbsp</label>
			<select class="select2 number-select" id="filter_cabang">

                <?php foreach($cabang as $b){
	            	if($b['level_cabang'] != '1'){
	             ?>

                <option value="<?php echo $b['kode_cabang']; ?>" <?php if($b['kode_cabang'] == user('kode_cabang')) echo ' selected'; ?>><?php echo $b['nama_cabang']; ?></option>

                <?php } }?>

			</select>  
    		<?php
                echo '<button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>';
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
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-12 col-12">
				<div class="card">
		    		<div class="card-header"><?php echo "Neraca"; ?></div>
					<div class="card-body">
						<div class="ContenedorTabla" id="neraca">
							<?php
							// echo '<pre>';
							// echo print_r($neraca['coa0']['1000000']);
							// echo '</pre>';
							table_open('',false);
								thead();
									tr('d-cabang-neraca d-bg-header');
										th('<red>-</red>','','colspan="4"');
										th('<red>-</red>','d-head','colspan="12"');
									tr('d-neraca d-bg-header');
										th('<span>'.lang('sandi bi').'</span>','','width="60"  class="text-center align-middle wd-100"');
										th('<span>'.lang('coa 5').'</span>','','width="60"  class="text-center align-middle wd-100"');
										th('<span>'.lang('coa 7').'</span>','','width="60"  class="text-center align-middle wd-100"');
										th('<span>'.lang('keterangan').'</span>','',' class="text-center align-middle wd-230"');
										for ($i=1; $i <=12 ; $i++) { 
											th('<red>-</red>','d-head','');
										}
								tbody();
							table_close();
							?>
						</div>
					</div>	
				</div>
			</div>
			<div class="col-sm-12 col-12">
				<br>
				<br>
				<div class="card">
		    		<div class="card-header"><?php echo "laba Rugi"; ?></div>
					<div class="card-body">
						<div class="ContenedorTabla" id="labarugi">
							<?php
							// echo '<pre>';
							// echo print_r($neraca['coa0']['1000000']);
							// echo '</pre>';
							table_open('',false);
								thead();
									tr('d-cabang-labarugi d-bg-header');
										th('<red>-</red>','','colspan="4"');
										th('<red>-</red>','d-head','colspan="12"');
									tr('d-labarugi d-bg-header');
										th('<span>'.lang('Kode').'</span>','','width="60"  class="text-center align-middle wd-100"');
										th('<span>'.lang('Keterangan').'</span>','','width="60"  class="text-center align-middle wd-100"');
										th('<span>'.lang('coa 7').'</span>','','width="60"  class="text-center align-middle wd-100"');
										th('<span>'.lang('keterangan').'</span>','',' class="text-center align-middle wd-230"');
										for ($i=1; $i <=12 ; $i++) { 
											th('<red>-</red>','d-head','');
										}
								tbody();
							table_close();
							?>
						</div>
					</div>	
				</div>
			</div>
			<div class="col-sm-12 col-12">
				<br>
				<br>
				<div class="card">
		    		<div class="card-header"><?php echo "Rekap Rasio"; ?></div>
					<div class="card-body">
						<div class="ContenedorTabla" id="rekaprasio">
							<?php
							// echo '<pre>';
							// echo print_r($neraca['coa0']['1000000']);
							// echo '</pre>';
							table_open('',false);
								thead();
									tr('d-cabang-rekaprasio d-bg-header');
										th('<red>-</red>','','colspan="2"');
										th('<red>-</red>','d-head','colspan="12"');
									tr('d-rekaprasio d-bg-header');
										th('<span>Kode</span>','','width="60"  class="text-center align-middle wd-100"');
										th('<span>keterangan</span>','','width="60"  class="text-center align-middle wd-230"');
										for ($i=1; $i <=12 ; $i++) { 
											th('<red>-</red>','d-head','');
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

<link rel="stylesheet" type="text/css" href="<?= base_url('assets/plugins/freeze_table/').'ScrollTabla.css' ?>">
<script type="text/javascript" src="<?= base_url('assets/plugins/freeze_table/').'jquery.CongelarFilaColumna.js' ?>"></script>
<script type="text/javascript">
// $("#neraca .table-app").CongelarFilaColumna({Columnas:4,coloreacelda:false,colorcelda:'#5882fa0a'});
var xhr_ajax = null;
$(document).ready(function(){
	loadColumnNeraca('neraca');
	loadColumnLabaRugi('labarugi');
	loadColumnRekap();
});
$('#filter_anggaran').change(function(){
	loadColumnNeraca('neraca');
	loadColumnLabaRugi('labarugi');
	loadColumnRekap();
});

$('#filter_cabang').change(function(){
	loadColumnNeraca('neraca');
	loadColumnLabaRugi('labarugi');
	loadColumnRekap();
});
function loadColumnNeraca(p1){
	$('#'+p1+' tbody').html('');	
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/budget_nett/neraca_column';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
    page += '/'+ p1;
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	if(res.status){
        		$('#'+p1+' .d-head').remove();
        		$('#'+p1+' .d-cabang-'+p1).append(res.cabang);
        		$('#'+p1+' .d-'+p1).append(res.month);
        		$('#'+p1+' tbody').append(res.view);
        		cLoader.close();
        		window['loadMore_'+p1](p1,0);
        	}else{
        		cAlert.open(res.message);
        		cLoader.close();
        	}
        	checkSubData();
		}
    });
}

var xhr_ajax6 = null;
function loadColumnLabaRugi(p1){
	$('#'+p1+' tbody').html('');	
    if( xhr_ajax6 != null ) {
        xhr_ajax6.abort();
        xhr_ajax6 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/budget_nett/neraca_column';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
    page += '/'+ p1;
  	xhr_ajax6 = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax6 = null;
        	if(res.status){
        		$('#'+p1+' .d-head').remove();
        		$('#'+p1+' .d-cabang-'+p1).append(res.cabang);
        		$('#'+p1+' .d-'+p1).append(res.month);
        		$('#'+p1+' tbody').append(res.view);
        		cLoader.close();
        		window['loadMore_'+p1](p1,0);
        	}else{
        		cAlert.open(res.message);
        		cLoader.close();
        	}
        	checkSubData();
		}
    });
}


var xhr_ajax3 = null;
function loadColumnRekap(){
	$('#rekaprasio tbody').html('');	
    if( xhr_ajax3 != null ) {
        xhr_ajax3.abort();
        xhr_ajax3 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/budget_nett/rekaprasio_column';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
    // page += '/'+ p1;
  	xhr_ajax3 = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax3 = null;
        	if(res.status){
        		$('#rekaprasio .d-head').remove();
        		$('#rekaprasio .d-cabang-rekaprasio').append(res.cabang);
        		$('#rekaprasio .d-rekaprasio').append(res.month);
        		$('#rekaprasio tbody').append(res.view);
        		cLoader.close();
        		loadMoreRekap(0);
        	}else{
        		cAlert.open(res.message);
        		cLoader.close();
        	}
        	checkSubData();
		}
    });
}
var xhr_ajax2 = null;
function loadMore_neraca(p1,count){
	if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/budget_nett/load_more';
  	xhr_ajax2 = $.ajax({
        url: page,
        type: 'post',
		data : {page:p1,count:count},
        dataType: 'json',
        success: function(res){
        	xhr_ajax2 = null;
        	console.log(res);
        	if(res.status){
        		$.each(res.view,function(k,v){
        			$(res.classnya).find(k).append(v);
        		});
        		cLoader.close();
        		window['loadMore_'+p1](p1,res.count);
        	}else{
        		if(res.total_gab){
        			$.each(res.total_gab,function(k,v){
        				$.each(v,function(k2,v2){
        					$(res.classnya).find('.d-'+p1+'-'+k+'-'+res.id+'-'+k2).html(v2);
        				})
        			})
        		}
        		cLoader.close();
        	}
        	
		}
    });
}

var xhr_ajax5 = null;
function loadMore_labarugi(p1,count){
	if( xhr_ajax5 != null ) {
        xhr_ajax5.abort();
        xhr_ajax5 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/budget_nett/load_more';
  	xhr_ajax5 = $.ajax({
        url: page,
        type: 'post',
		data : {page:p1,count:count},
        dataType: 'json',
        success: function(res){
        	xhr_ajax5 = null;
        	console.log(res);
        	if(res.status){
        		$.each(res.view,function(k,v){
        			$(res.classnya).find(k).append(v);
        		});
        		cLoader.close();
        		window['loadMore_'+p1](p1,res.count);
        	}else{
        		if(res.total_gab){
        			$.each(res.total_gab,function(k,v){
        				$.each(v,function(k2,v2){
        					$(res.classnya).find('.d-'+p1+'-'+k+'-'+res.id+'-'+k2).html(v2);
        				})
        			})
        		}
        		cLoader.close();
        	}
        	
		}
    });
}

var xhr_ajax4 = null;
function loadMoreRekap(count){
	if( xhr_ajax4 != null ) {
        xhr_ajax4.abort();
        xhr_ajax4 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/budget_nett/load_more_rekap';
  	xhr_ajax4 = $.ajax({
        url: page,
        type: 'post',
		data : {count:count},
        dataType: 'json',
        success: function(res){
        	xhr_ajax2 = null;
        	if(res.status){
        		$.each(res.view,function(k,v){
        			$(res.classnya).find(k).append(v);
        		});
        		cLoader.close();
        		loadMoreRekap(res.count);
        	}else{
        		cLoader.close();
        	}
        	
		}
    });
}

$(document).on('click','.btn-export',function(){
    var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));

    var dt_neraca = get_data_table('#neraca');
    var arr_neraca = dt_neraca['arr'];
    var arr_neraca_header = dt_neraca['arr_header'];

    var dt_laba_rugi = get_data_table('#labarugi');
    var arr_labarugi = dt_laba_rugi['arr'];
    var arr_labarugi_header = dt_laba_rugi['arr_header'];

    var post_data = {
        "neraca_header" : JSON.stringify(arr_neraca_header),
        "neraca"        : JSON.stringify(arr_neraca),
        "labarugi_header" : JSON.stringify(arr_labarugi_header),
        "labarugi"        : JSON.stringify(arr_labarugi),
        "kode_anggaran" : $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   : $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "csrf_token"    : x[0],
    }
    var url = base_url + 'transaction/budget_nett/export';
    $.redirect(url,post_data,"","_blank");
});
function get_data_table(classnya){
    var arr = [];
    var arr_header = [];
    var no = 0;
    $(classnya+" table tr").each(function() {
        var arrayOfThisRowHeader = [];
        var tableDataHeader = $(this).find('th');
        if (tableDataHeader.length > 0) {
            tableDataHeader.each(function(k,v) {
                arrayOfThisRowHeader.push($(this).text()); 
                if(no == 0 && k == 0){
                    for(var i=1;i<=3;i++){
                        arrayOfThisRowHeader.push("");
                    }
                }else if(no == 0){
                    for(var i=1;i<=11;i++){
                        arrayOfThisRowHeader.push("");
                    }
                }
            });
            arr_header.push(arrayOfThisRowHeader);
            if(no == 1){
                arr.push(arrayOfThisRowHeader);
            }
            no++; 
        }

        var arrayOfThisRow = [];
        var tableData = $(this).find('td');
        if (tableData.length > 0) {
            tableData.each(function() { arrayOfThisRow.push($(this).text()); });
            arr.push(arrayOfThisRow);
        }
    });
    return {'arr' : arr, 'arr_header' : arr_header};
}

// input
$(document).on('dblclick','.table-app tbody td .badge',function(){
    if($(this).closest('tr').find('.btn-input').length == 1) {
        var badge_status    = '0';
        var data_id         = $(this).closest('tr').find('.btn-input').attr('data-id');
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
$(document).on('click','.btn-save',function(){
    var i = 0;
    $('.edited').each(function(){
        i++;
    });
    if(i == 0) {
        cAlert.open('tidak ada data yang di ubah');
    } else {
        var msg     = lang.anda_yakin_menyetujui;
        if( i == 0) msg = lang.anda_yakin_menolak;
        cConfirm.open(msg,'save_perubahan');        
    }

});
var n_neraca    = 0;
var n_labarugi  = 0;
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
        var post_name = $(this).attr('data-id').split('-');
        if(post_name[0] == 'neraca'){
            n_neraca = 1;
        }else if(post_name[0] == 'labarugi'){
            n_labarugi = 1;
        }
    });
    
    var jsonString = JSON.stringify(data_edit);
    $.ajax({
        url : base_url + 'transaction/budget_nett/save_perubahan',
        data    : {
            'json' : jsonString,
            verifikasi : i,
            'kode_anggaran' : $('#filter_anggaran option:selected').val(),
        },
        type : 'post',
        success : function(response) {
            cAlert.open(response,'success','checkReload');
        }
    })
}
function checkReload(){
    if(n_neraca == 1){
        loadColumnNeraca('neraca');
    }
    if(n_labarugi == 1){
        loadColumnLabaRugi('labarugi');
    }

    n_neraca = 0;
    n_labarugi = 0;
}
function formatCurrency(angka, prefix,decimal){
    min_txt     = angka.split("-");
    str_min_txt = '';
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
    split           = number_string.split(','),
    sisa            = split[0].length % 3,
    rupiah          = split[0].substr(0, sisa),
    ribuan          = split[0].substr(sisa).match(/\d{3}/gi);

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
</script>