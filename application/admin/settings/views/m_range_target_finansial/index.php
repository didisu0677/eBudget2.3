<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php echo access_button('delete,active,inactive,export,import'); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<?php
	table_open('',true,base_url('settings/m_range_target_finansial/data'),'tbl_m_range_target_finansial');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('nama'),'','data-content="nama"');
				// th(lang('mulai_dari'),'text-right','data-content="mulai" data-type="currency"');
				th(lang('sampai'),'text-right','data-content="sampai" data-type="currency"');
				th(lang('urutan'),'text-right','data-content="urutan" data-type="number"');
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form');
	modal_body();
		form_open(base_url('settings/m_range_target_finansial/save'),'post','form');
			col_init(3,9);
			input('hidden','id','id');
			// input('money',lang('mulai_dari'),'mulai');
			input('money',lang('sampai'),'sampai');
			input('number',lang('urutan'),'urutan','required');
			toggle(lang('aktif').'?','is_active');
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/m_range_target_finansial/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
