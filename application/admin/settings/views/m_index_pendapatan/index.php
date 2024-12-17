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
	table_open('',true,base_url('settings/m_index_pendapatan/data'),'tbl_m_index_pendapatan');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('coa'),'','data-content="coa"');
				th(lang('nama'),'','data-content="nama"');
				th(lang('index_kali'),'text-right','data-content="index_kali" data-type="currency"');
				th(lang('keterangan'),'','data-content="keterangan"');
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form','','','data-openCallback="formOpen"');
	modal_body('style-select2');
		form_open(base_url('settings/m_index_pendapatan/save'),'post','form');
			col_init(3,9);
			input('hidden','id','id');
			select2(lang('coa'),'coa','required');
			input('text',lang('nama'),'nama','','','data-readonly="true"');
			input('text',lang('index_kali'),'index_kali');
			textarea(lang('keterangan'),'keterangan');
			toggle(lang('aktif').'?','is_active');
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/m_index_pendapatan/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
$(function(){
	$('.btn-act-template').attr('href',base_url+"settings/m_index_pendapatan/template");
	$('.btn-act-export').attr('href',base_url+"settings/m_index_pendapatan/export");
	$('#index_kali').addClass('money2');
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
function formOpen(){
	money_init();
}
</script>