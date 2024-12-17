<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<label class=""><?php echo 'Anggaran'; ?>  &nbsp</label>
			<select class="select2 infinity custom-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select>

			<?php
			if($access['access_input']):
			echo '<button class="btn btn-info btn-refresh" title="'.lang('cek_kolom_cabang').'">'.lang('cek_kolom_cabang').'</button>';
			endif;
			echo access_button('delete,active,inactive,export,import'); 
			?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<?php
	table_open('',true,base_url('settings/rate/data'),'tbl_rate');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('kode_anggaran'),'','data-content="kode_anggaran"');
				th(lang('jenis_rate'),'','data-content="jenis_rate"');
				th(lang('no_coa'),'','data-content="no_coa"');
				th(lang('nama_coa'),'','data-content="nama_coa"');
				foreach($cabang as $v){
					th($v,'','data-content="'.$v.'"');
				}
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form');
	modal_body();
		form_open(base_url('settings/rate/save'),'post','form');
			col_init(3,9);
			input('hidden','id','id');
			input('text',lang('jenis_rate'),'jenis_rate');
			input('text',lang('no_coa'),'no_coa');
			input('text',lang('nama_coa'),'nama_coa');
			foreach($cabang as $v){
				input('number',$v,$v);
			}
			toggle(lang('aktif').'?','is_active');
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/rate/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
var xhr_ajax = null;
$('.btn-refresh').on('click',function(){
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoading.open();
	$.ajax({
		url 	: base_url+'settings/rate/check_cabang',
		type	: 'post',
		data 	: {
			kode_anggaran : $('#filter_anggaran option:selected').val(),
		},
		dataType: 'json',
		success	: function(response) {
			cLoading.close();
			cAlert.open(response.message,response.status,response.load);
		}
	});
})
</script>
