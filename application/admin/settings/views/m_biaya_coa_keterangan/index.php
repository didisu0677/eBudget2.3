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
	table_open('',true,base_url('settings/m_biaya_coa_keterangan/data'),'tbl_m_biaya_coa_keterangan');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('coa'),'','data-content="coa"');
				th(lang('nama'),'','data-content="nama"');
				th(lang('keterangan'),'','data-content="keterangan"');
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form');
	modal_body('style-select2');
		form_open(base_url('settings/m_biaya_coa_keterangan/save'),'post','form');
			col_init(3,9);
			input('hidden','id','id');
			select2(lang('coa'),'coa','required');
			input('text',lang('nama'),'nama','','','data-readonly="true"');
			input('text',lang('keterangan'),'keterangan','','<- '.lang('harus_diisi'));
			toggle(lang('aktif').'?','is_active');
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/m_biaya_coa_keterangan/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
$(function(){
	$.ajax({
		url 	: base_url+'api/coa_option',
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			$('#coa').html(response.data);
		}
	});
})
$('#coa').on('change',function(){
	var val = $(this).find('option:selected').text();
	val = val.split(' - ');
	$('#nama').val(val[1]);
})
</script>