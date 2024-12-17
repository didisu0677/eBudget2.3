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
			               		
			<label class="">&nbsp <?php echo lang('coa'); ?>  &nbsp</label>

			<select class="select2 custom-select cookie_coa" id="filter_coa">
				<?php foreach ($coa as $v) {
					echo '<option value="'.$v->glwnco.'">'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</option>';
				} ?>
			</select>
			<button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>
			<?php
			$arr = [
				    ['btn-export','Export Data','fa-download'],
				    ['btn-import','Import Data','fa-upload'],
				    ['btn-act-template','Template Import','fa-file-alt']
				];
				echo access_button('',$arr);
			?>
		</div>
		<div class="clearfix"></div>	
	</div>
	<?php $this->load->view($sub_menu); ?>
</div>
<div class="content-body">
	<?php
	$this->load->view($sub_menu);
	echo '<div id="d-content">';
	table_open('table table-bordered table-app table-1');
		thead();
			tr();
				th(get_view_report(1),'','width="60" colspan="'.(count($detail_tahun)+2).'" class="text-left"');
			tr();
				th(lang('kode_cabang'),'','class="text-center align-middle" style="min-width:80px"');
				th(lang('cabang'),'','class="text-center align-middle" style="width:auto;min-width:330px"');

				foreach ($kolom as $v) {
					$column = month_lang($v->bulan).' '.$v->tahun;
					$column .= '<br> ('.$v->singkatan.')';
					th($column,'','class="text-center" style="min-width:150px"');
				}
		
		tbody();
	table_close();
	echo '</div>';
	?>
	
</div>
<?php 

modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/index_besaran/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			select2('Dalam bentuk','currency','required');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
var controller = '<?= $controller ?>';
$( document ).ready(function() {
    getData();
});
$('#filter_coa').on('change',function(){
	getData();
});

$('.btn-import').click(function(){
	$('#form-import')[0].reset();

    $('#modal-import .alert').hide();
    $('#modal-import').modal('show');
    $('.fileupload-preview').html('');
    var val = $('#currency option').eq(0).val();
    if(val){
    	$('#currency').val(val).trigger('change');
    }
});

$(document).on('click','.btn-export',function(){
	var currentdate = new Date(); 
	var datetime = currentdate.getDate() + "/"
	                + (currentdate.getMonth()+1)  + "/" 
	                + currentdate.getFullYear() + " @ "  
	                + currentdate.getHours() + ":"  
	                + currentdate.getMinutes() + ":" 
	                + currentdate.getSeconds();
	
	$('.bg-c1').each(function(){
		$(this).attr('bgcolor','#ababab');
	});
	$('.bg-c2').each(function(){
		$(this).attr('bgcolor','#d0d0d0');
	});
	$('.bg-c3').each(function(){
		$(this).attr('bgcolor','#f5f5f5');
	});
	var table	= '';
	table += '<table border="1">';
	table += $('.content-body #d-content').html();
	table += '</table>';
	var target = table;
	// window.open('data:application/vnd.ms-excel,' + encodeURIComponent(target));
	let file = new Blob([target], {type:"application/vnd.ms-excel"});
	let url = URL.createObjectURL(file);
	let a = $("<a />", {
	  href: url,
	  download: "indek-besaran-hasil"+formatDate(new Date())+".xlsx"
	})
	.appendTo("body")
	.get(0)
	.click();
	$('.bg-c1,.bg-c2,.bg-c3').each(function(){
		$(this).removeAttr('bgcolor');
	});
});
function getData(){
	var tahun_anggaran = $('#filter_anggaran option:selected').val();
	var coa = $('#filter_coa').val();
	var page = base_url + 'settings/index_besaran/dataHasil';
	page 	+= '/'+tahun_anggaran;
	page 	+= '/'+coa;

	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'settings/index_besaran/dataHasil';
	page 	+= '/'+tahun_anggaran;
	page 	+= '/'+coa;

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			$('.table-app tbody').html(response.table);
			cLoader.close();
			cek_autocode();
			fixedTable();
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
// $(document).on('keyup','.edit-value',function(e){
// 	var wh 			= e.which;
// 	if((48 <= wh && wh <= 57) || (96 <= wh && wh <= 105) || wh == 8) {
// 		if($(this).text() == '') {
// 			$(this).text('');
// 		} else {
// 			var n = parseInt($(this).text().replace(/[^0-9\-]/g,''),10);
// 		    $(this).text(n.toLocaleString());
// 		    var selection = window.getSelection();
// 			var range = document.createRange();
// 			selection.removeAllRanges();
// 			range.selectNodeContents($(this)[0]);
// 			range.collapse(false);
// 			selection.addRange(range);
// 			$(this)[0].focus();
// 		}
// 	}
// });
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
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] =  $(this).text().replace(/[^0-9\-]/g,'');
		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);	
	var tahun_anggaran = $('#filter_anggaran option:selected').val();
	var coa = $('#filter_coa').val();
	var page = base_url + 'settings/index_besaran/save_perubahan_hasil';
	page 	+= '/'+tahun_anggaran;
	page 	+= '/'+coa;	
	$.ajax({
		url : page,
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
// $(document).on('click','.btn-export',function(){
//     var hashids = new Hashids(encode_key);
//     var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
//     var cabang = $('#filter_cabang').val();
//     var dt_neraca = get_data_table('.content-body');
//     var arr_neraca = dt_neraca['arr'];
//     var arr_neraca_header = dt_neraca['arr_header'];

//     var post_data = {
//         "header" : JSON.stringify(arr_neraca_header),
//         "data"        : JSON.stringify(arr_neraca),
//         "coa"			: $('#filter_coa option:selected').val(),
//         "kode_anggaran" : $('#filter_anggaran option:selected').val(),
//         "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
//         "csrf_token"    : x[0],
//         'page' 			: 'hasil',
//     }
//     var url = base_url + 'transaction/'+controller+'/export';
//     $.redirect(url,post_data,"","_blank");
// });
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
            tableData.each(function() { arrayOfThisRow.push($(this).text()); });
            arr.push(arrayOfThisRow);
        }
    });
    return {'arr' : arr, 'arr_header' : arr_header};
}
$('.btn-act-template').on('click',function(){
	window.open(base_url+'settings/index_besaran/template/hasil', '_blank');
})
</script>