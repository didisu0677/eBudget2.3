<div class="content-header page-data" data-additional="<?= 1 ?>">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<label class=""><?php echo lang('tahun'); ?>  &nbsp</label>

			<select class="select2 custom-select" id="filter_tahun">
				<?php foreach ($tahun as $v) { ?>
                <option value="<?= $v->tahun ?>">Data Core Tahun <?= $v->tahun ?></option>
                <?php } ?>
			</select>

			<?php
    		echo filter_cabang_admin(1,$cabang,['kanpus' => 1]);
    		echo ' <button class="btn btn-info btn-refresh" href="javascript:;" title="Digunakan untuk mengambil data dari server secara realtime" > '.lang('pilih').' </button>';
    		?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body mt-6">
	<div class="main-container mt-2">
		<div class="main-container">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header text-center"><?= $title ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1" data-height="80">
	    					<table class="table table-striped table-bordered table-app table-hover">
	    						<thead>
	    							<tr><th colspan="14"><?= get_view_report() ?></th></tr>
	    							<tr>
	    								<th class="text-center"><?= lang('coa') ?></th>
	    								<th class="text-center" style="min-width:250px"><?= lang('keterangan') ?></th>
	    								<?php
	    								for ($i=1; $i <= 12 ; $i++) { 
	    									echo '<th class="text-center" style="min-width:150px">'.month_lang($i).'</th>';
	    								}
	    								?>
	    							</tr>
	    						</thead>
	    						<tbody></tbody>
	    					</table>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>
</div>
<script type="text/javascript">
var xhr_ajax = null;
$(function(){
	resize_window();
})
$(document).on('click','.btn-refresh',function(){
    getData();
});
function getData(){
	var cabang = $('#filter_cabang').val();
	if(!cabang){
		return '';
	}
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'settings/data_core/data';
    page += '/'+ $('#filter_tahun').val();
    page += '/'+ cabang;
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : {},
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	$('#result1 tbody').html(res.table);
        	cLoader.close();
        	checkSubData();
		}
    });
}
</script>