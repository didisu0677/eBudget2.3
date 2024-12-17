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
			<?php echo access_button('export,import'); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<?php
	table_open('',true,base_url('settings/import_npl/data'),'tbl_import_npl');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('kode_anggaran'),'','data-content="kode_anggaran"');
				th(lang('keterangan'),'','data-content="keterangan_anggaran"');
				th(lang('tanggal_import'),'','data-content="create_at" data-type="daterange"');
				th(lang('import_oleh'),'','data-content="create_by"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>

<?php 
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/import_npl/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
$('.btn-import').click(function(){
	$('#form-import')[0].reset();
    $('#modal-import .alert').hide();
    $('#modal-import').modal('show');
    $('.fileupload-preview').html('');
});
$(document).on('click','.btn-detail',function(){
	$.get(base_url + 'settings/import_npl/detail/' + $(this).attr('data-id'),function(res){
		cInfo.open(lang.detil,res,{modal_lg:true});
	});
});
</script>