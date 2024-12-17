<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<label class="">Anggaran  &nbsp</label>
			<select class="select2 infinity custom-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select> 
			<?php echo access_button('delete,active,inactive,export,import'); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<?php
	table_open('',true,base_url('settings/m_biaya_rkf/data'),'tbl_m_biaya_rkf');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('glwnob'),'','data-content="glwnob" data-alias="glwnob" data-table="b" data-field="b.glwnob"');
				th(lang('glwnco'),'','data-content="coa"');
				th(lang('nama'),'','data-content="glwdes" data-alias="glwdes" data-table="b" data-field="b.glwdes"');
				th('Default','text-center','data-content="is_default" data-type="boolean"');
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form','','',' data-openCallback="formOpen"');
	modal_body();
		form_open(base_url('settings/m_biaya_rkf/save'),'post','form');
			col_init(3,9);
			input('hidden','id','id');
			echo '<div class="table-responsive">
				    <table class="table table-bordered" id="table_coa">
						<thead>
							<tr>
								<th class="text-center">'.lang('akun_coa').'</th>
								<th class="text-center">Default</th>
								<th width="10">
									<button type="button" class="btn btn-sm btn-icon-only btn-success btn-add-item"><i class="fa-plus"></i></button>
								</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>';
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/m_biaya_rkf/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>

<script type="text/javascript">
var xhr_ajax 	= null;
var dt_coa 		= [];
$(document).ready(function(){
	get_coa();
})
function formOpen(){
	dt_index = 0;
	$('#table_coa tbody').html('');
	response_data = response_edit;
	var length = jQuery.isEmptyObject(response_data);
	if(!length){
		add_item();
		var f = $('#table_coa tbody tr').last();
		f.find('.coa').val(response_data.coa).trigger('change');
		f.find('.dt_id').val(response_data.id);
		if(response_data.is_default == 1){
			f.find('.is_default').prop('checked',true);
		}else{
			f.find('.is_default').prop('checked',false);
		}
		$('.btn-add-item, .btn-delete-item').hide();
	}else{
		add_item();
		$('.btn-add-item, .btn-delete-item').show();
	}
}
function get_coa(){
	var url = base_url+"api/coa_option";
	cLoader.open(lang.memuat_data + '...');
	xhr_ajax = $.ajax({
		url 	: url,
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			dt_coa = response.data;
			cLoader.close();
		}
	});
}
$(document).on('click','.btn-add-item',function(){
	add_item();
});
$(document).on('click','.btn-delete-item',function(){
	$(this).closest('tr').remove();
});
var dt_index = 0;
function add_item(){
	dt_index += 1;
	item = `<tr>`;
	item += `<td class="style-select2"><input class="dt_id" type="hidden" name="dt_id[]" /><input type="hidden" class="dt_key" value="`+dt_index+`" name="dt_key[]"/><select style="width:100%" class="form-control pilihan coa" name="coa[]" data-validation="required">`+dt_coa+`</select></td>`;
	item += '<td class="text-center"><div class="custom-checkbox custom-control custom-control-inline"><input class="custom-control-input chk-child is_default" id="is_default'+dt_index+'" type="checkbox"name="is_default'+dt_index+'[]" value="1"> <label class="custom-control-label" for="is_default'+dt_index+'">Ya</label></div></td>';
	item += '<td><button type="button" class="btn btn-sm btn-icon-only btn-danger btn-delete-item"><i class="fa-times"></i></button></td>';
	item += '</tr>';
	$('#table_coa tbody').append(item);
	var $t = $('#table_coa .pilihan').last();
	$.each($t,function(k,o){
		var $o = $(o);
		$o.select2({
			dropdownParent : $o.parent(),
			placeholder : ''
		});
	});
}
</script>