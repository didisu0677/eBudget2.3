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
	table_open('',true,base_url('settings/m_tabungan_biaya_hadiah/data'),'tbl_m_tabungan_biaya_hadiah');
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('coa'),'','data-content="coa"');
				th(lang('nama'),'','data-content="nama"');
				th(lang('struktur_cabang'),'','data-content="struktur_cabang"');
				for ($i=1; $i <= 12 ; $i++) { 
					$field = 'B_'.sprintf("%02d", $i);
					th(month_lang($i).' ('.get_view_report().')','text-right','data-content="'.$field.'" data-type="currency"');
				}
				th(lang('keterangan'),'','data-content="keterangan"');
				th(lang('aktif').'?','text-center','data-content="is_active" data-type="boolean"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-form');
	modal_body('style-select2');
		form_open(base_url('settings/m_tabungan_biaya_hadiah/save'),'post','form');
			col_init(3,9);
			input('hidden','id','id');
			select2(lang('coa'),'coa','required',$coa,'glwnco','glwdes');
			input('text',lang('nama'),'nama','','','data-readonly="true"');
			select2(lang('struktur_cabang'),'id_struktur_cabang','required',$struktur_cabang,'id','struktur_cabang');
			input('hidden',lang('struktur_cabang'),'struktur_cabang','','','data-readonly="true"');
			for ($i=1; $i <= 12 ; $i++) { 
				$field = 'B_'.sprintf("%02d", $i);
				input('money',month_lang($i).' ('.get_view_report().')',$field);
			}
			textarea(lang('keterangan'),'keterangan');
			toggle(lang('aktif').'?','is_active');
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
modal_open('modal-import',lang('impor'));
	modal_body('style-select2');
		form_open(base_url('settings/m_tabungan_biaya_hadiah/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			select2('Dalam bentuk','currency','required');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script type="text/javascript">
$('#coa').on('change',function(){
	var txt = $(this).find('option:selected').text();
	var arr = txt.split(' - ');
	if(arr.length>1){
		txt = arr[1];
	}
	$('#nama').val(txt);
})
$('#id_struktur_cabang').on('change',function(){
	var txt = $(this).find('option:selected').text();
	$('#struktur_cabang').val(txt);
})
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
$('.btn-act-import').click(function(){
    var val = $('#currency option').eq(0).val();
    if(val){
    	$('#currency').val(val).trigger('change');
    }
});
</script>