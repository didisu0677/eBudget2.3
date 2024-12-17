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
	table_open('',true,base_url('settings/m_monly_performance_item/data'),'tbl_m_monly_performance_item');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('coa'),'','data-content="coa"');
				th(lang('nama_coa'),'','data-content="nama"');
				th(lang('grup'),'','data-content="grup"');
				th('#','text-center','width="30" data-content="urutan"');
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form','','',' data-openCallback="formOpen"');
	modal_body();
		form_open(base_url('settings/m_monly_performance_item/save'),'post','form','class="form-select"');
			col_init(3,9);
			input('hidden','id','id');
			select2(lang('coa'),'coa','required',$coa,'glwnco','glwdes');
			input('text',lang('nama_coa'),'nama','','','data-readonly="true"');
			input('text',lang('grup'),'grup');
			input('text',lang('urutan'),'urutan','required');
			toggle(lang('clone_penc').'?','clone_penc');
			select2(lang('coa'),'coa_clone_penc','',$data_clone,'coa','glwdes');
			toggle(lang('clone_pert').'?','clone_pert');
			select2(lang('coa'),'coa_clone_pert','',$data_clone,'coa','glwdes');
			toggle(lang('aktif').'?','is_active');
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/m_monly_performance_item/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
$('#coa').on('change',function(){
	var txt = $(this).find('option:selected').text();
	txt = txt.split(' - ');
	$('#nama').val(txt[1]);
})
function formOpen(){
	$('#clone_penc').prop('checked',false).trigger('change');
	$('#clone_pert').prop('checked',false).trigger('change');
}
$('#clone_penc').on('change',function(){
	var val  	= $(this).is(':checked');
	var index 	= $('#coa_clone_penc').closest('.form-group');
	index.find('.error').empty();
	index.find('span').removeClass('error');
	if(val){
		$('#coa_clone_penc').attr('data-validation','required');
		index.show();
	}else{
		$('#coa_clone_penc').removeAttr('data-validation');
		index.hide();
	}
})
$('#clone_pert').on('change',function(){
	var val  	= $(this).is(':checked');
	var index 	= $('#coa_clone_pert').closest('.form-group');
	index.find('.error').empty();
	index.find('span').removeClass('error');
	if(val){
		$('#coa_clone_pert').attr('data-validation','required');
		index.show();
	}else{
		$('#coa_clone_pert').removeAttr('data-validation');
		index.hide();
	}
})
</script>