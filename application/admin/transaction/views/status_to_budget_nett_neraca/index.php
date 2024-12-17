<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<label class=""><?php echo lang('anggaran'); ?>  &nbsp</label>
			<select class="select2 infinity number-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-12 col-12">
				<div class="card d-content">
		    		<div class="card-header text-center"><?= $title ?></div>
					<div class="card-body"></div>
				</div>
			</div>
		</div>			
	</div>
</div>
<script type="text/javascript">
var xhr_ajax = null;
var controller = '<?= $controller ?>';
$(function(){
	loadData();
})

function loadData(){
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;

        	if(!res.status){
        		cLoader.close();
        		$('.d-content .card-body').html('');
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$('.d-content .card-body').html(res.view);
        	$('.d-content .card-header').html(res.title);
    		resize_window();
    		checkSubData();
    		cLoader.close();
		}
    });
}
</script>
