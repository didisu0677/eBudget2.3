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
			<button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>
			
    		<?php 
				$arr = [
				    ['btn-export','Export Data','fa-upload'],
				    ['btn-import','Import Data','fa-upload'],
				    ['btn-act-template','Template Import','fa-file-alt']
				];
				echo access_button('',$arr); 
			?>
		</div>
		<div class="clearfix"></div>	
	</div>
</div>
<div class="content-body">
	<?php
	table_open('table table-bordered table-app table-1',true,base_url('settings/index_besaran_biaya/data'));
		thead();
			tr();
				th(get_view_report(1),'','width="60" colspan="'.(count($detail_tahun)+1).'" class="text-left"');
			tr();
				th('Coa','','class="text-center align-middle" style="width:auto;min-width:80px"');
				th(lang('keterangan'),'','class="text-center align-middle" style="width:auto;min-width:330px"');

				for($a=1;$a<=12;$a++){
					th(month_lang($a),'','class="text-center" style="min-width:100px"');
				}		
		tbody();
	table_close();
	?>
</div>
<?php
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/index_besaran_biaya/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
$( document ).ready(function() {
    getData();
});
$('#filter_coa').on('change',function(){
	getData();
});
$(document).on('click','.btn-export',function(){
	window.open(base_url+'settings/index_besaran_biaya/export', '_blank');
});
function getData(){
	var tahun_anggaran = $('#filter_anggaran option:selected').val();
	var page = base_url + 'settings/index_besaran_biaya/data';
	page 	+= '/'+tahun_anggaran;

	cLoader.open(lang.memuat_data + '...');
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
	var tahun_anggaran = $('#filter_anggaran option:selected').val();
	var page = base_url + 'settings/index_besaran_biaya/save_perubahan';
	page 	+= '/'+tahun_anggaran;
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
$(document).on('click','.btn-act-template',function(){
	window.open(base_url+'settings/index_besaran_biaya/template', '_blank');
});
$('.btn-import').click(function(){
	$('#form-import')[0].reset();
	$('#form-import').find('.fileupload-preview').empty();

    $('#modal-import .alert').hide();
    $('#modal-import').modal('show');
    var val = $('#currency option').eq(0).val();
    if(val){
    	$('#currency').val(val).trigger('change');
    }

});
</script>