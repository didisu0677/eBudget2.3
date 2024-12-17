<link rel="stylesheet" href="<?php echo base_url('assets/css/jquery.sortable.css'); ?>" />
<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php if($menu_access['access_edit']) { ?>
			<button type="button" class="btn btn-success btn-sm btn-sort"><i class="fa-align-right"></i><?php echo lang('atur_posisi'); ?></button>
			<?php } ?>
			<?php echo access_button('delete,active,inactive,export,import'); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<?php
	table_open('',true,base_url('settings/m_rkf_kolom/data'),'tbl_m_rkf_kolom');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('kode'),'','data-content="lang"');
				th(lang('kolom'),'','data-content="nama"');
				th(lang('urutan'),'text-right','data-content="urutan" data-type="currency"');
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form');
	modal_body();
		form_open(base_url('settings/m_rkf_kolom/save'),'post','form');
			col_init(3,9);
			input('hidden','id','id');
			input('text',lang('kode'),'lang','','','data-readonly="true"');
			input('text',lang('kolom'),'nama','','','data-readonly="true"');
			input('text',lang('urutan'),'urutan');
			toggle(lang('aktif').'?','is_active');
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-sort',lang('atur_posisi'),'modal-lg','modal-info');
	modal_body();
	modal_footer();
		echo '<form><button type="submit" class="btn btn-success" id="save-posisi">'.lang('simpan').'</button></form>';
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/m_rkf_kolom/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript" src="<?php echo base_url('assets/js/jquery.sortable.min.js'); ?>"></script>
<script type="text/javascript">
$('.btn-sort').click(function(){
	$('#modal-sort .modal-body').html('');
	$.ajax({
		url : base_url + 'settings/m_rkf_kolom/sortable',
		type : 'get',
		dataType : 'json',
		success : function(response) {
			$('#modal-sort .modal-body').html(response.content);
			$('#modal-sort').modal();
			$('ol.sortable').nestedSortable({
				forcePlaceholderSize: true,
				handle: 'div',
				helper:	'clone',
				items: 'li',
				opacity: .6,
				placeholder: 'placeholder',
				revert: 250,
				tabSize: 25,
				tolerance: 'pointer',
				toleranceElement: '> div',
				maxLevels: 4,
				isTree: true,
				expandOnHover: 700,
				isAllowed: function(item, parent, dragItem) {
					var x = true;
					if(dragItem.hasClass('module')) {
						if(typeof parent != 'undefined') x = false;
					} else {
						if(typeof parent == 'undefined') x = false;
						if(x && parent.closest('.module').attr('data-module') != dragItem.attr('data-module')) x = false;
					}
					return x;
				}
			});
		}
	});
});
$('#save-posisi').click(function(e){
	e.preventDefault();
	var serialized = $('ol.sortable').nestedSortable('serialize');
	$.ajax({
		url : base_url + 'settings/m_rkf_kolom/save_sortable',
		type : 'post',
		data : serialized,
		dataType : 'json',
		success : function(response) {
			if(response.status == 'success') {
				cAlert.open(response.message,response.status,'refreshData');
			} else {
				cAlert.open(response.message,response.status);
			}
		}
	});
});
</script>