<style type="text/css">
form .select2-container--default .select2-selection--single{
	min-width: auto !important;
	width: auto !important;
}
</style>
<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?= access_button('import',[],true); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<?php
	table_open('',true,base_url('settings/'.$controller.'/data'),$table);
		thead();
			tr();
				th('checkbox','text-center','width="30" data-content="id"');
				th(lang('periode'),'','data-content="periode"');
				th(lang('tanggal_import'),'','data-content="tanggal" data-type="daterange"');
				th(lang('import_oleh'),'','data-content="create_by"');
				th(lang('update_terakhir'),'','data-content="create_at"');
				th('&nbsp;','','width="30" data-content="action_button"');
	table_close();
	?>
</div>
<?php 
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('settings/'.$controller.'/import'),'post','form-import');?>
			<div class="form-group row">
				<label class="col-form-label col-sm-3 required"><?php echo lang('periode'); ?></label>
				<div class="col-md-4 col-6">
					<select id="periode_import" class="form-control select2" name="periode_import" data-validation=""  >
						<option value=""></option>
						<?php for($i = 1; $i <= 12; $i++){ ?>
                        <option value="<?php echo $i; ?>"<?php if($i == date('m')) echo ' selected'; ?>><?php echo month_lang($i); ?></option>
                        <?php } ?>
					</select>
				</div>
				<label class="col-form-label col-sm-2 required"><?php echo lang('tahun'); ?></label>
				<div class="col-md-3 col-4">
					<select id="tahun_import" class="form-control select2" name="tahun_import" data-validation=""  >
						<option value=""></option>
						<?php for($i = date('Y'); $i >= date('Y')-3; $i--){ ?>
                        <option value="<?php echo $i; ?>"<?php if($i == date('Y')) echo ' selected'; ?>><?php echo $i; ?></option>
                        <?php } ?>
					</select>
				</div>
			</div>
		<?php
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script>
var controller = '<?= $controller ?>';
$(document).on('click','.btn-detail',function(){
	$.get(base_url + 'settings/'+controller+'/detail/' + $(this).attr('data-id'),function(res){
		cInfo.open(lang.detil,res,{modal_lg:true});
	});
});
</script>