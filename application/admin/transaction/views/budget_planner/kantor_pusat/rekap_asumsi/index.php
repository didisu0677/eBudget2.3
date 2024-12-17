<style type="text/css">
	.wd-100{
		width: 100px !important;
		min-width: 100px !important;
		max-width: 100px !important;
	}
	.wd-150{
		width: 150px !important;
		min-width: 150px !important;
		max-width: 150px !important;
	}
	.wd-230{
		width: 350px !important;
		min-width: 350px !important;
		max-width: 350px !important;
	}
	.d-bg-header th{
		/*background-color: #e64a19 !important;*/
	}
	.d-bg-header span{
		/*color: #fff !important;*/
	}
	.d-bg-header red{
		color: #f7f7f7 !important;
	}
	.custom-nav li{
		max-width: 100% !important;
	}
</style>
<div class="content-header  page-data" data-additional="<?= $access_additional ?>" data-type="divisi" data-status_group="<?= $status_group ?>">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<label class=""><?php echo lang('anggaran'); ?>  &nbsp</label>
			<select class="select2 infinity custom-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select>
			<?php
			input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
			echo $option;
			$arr = [
			    ['btn-export','Export Data','fa-upload'],
			];
			echo access_button('',$arr);
			?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php $this->load->view($sub_menu); ?>
</div>
<div class="content-body mt-6">
<?php $this->load->view($sub_menu); ?>
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center"><?= $title ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result">
	    					<?php 
							table_open('',false);
								thead('sticky-top');
									tr();
										tr();
										th(lang('no'),'','width="60" class="text-center align-middle"');
										th(lang('divisi'),'','class="text-center align-middle"');
										th(lang('uraian'),'','style="min-width:150px" class="text-center align-middle"');
										th(lang('tipe'),'text-center align-middle wd-150');
										th(lang('grup'),'text-center align-middle wd-150');
										th(lang('kode_inventaris'),'text-center align-middle wd-150');
										th(lang('coa'),'text-center align-middle wd-150');
										th(lang('kantor_cabang'),'','class="text-center align-middle"');
										foreach ($detail_tahun as $k2 => $v2) {
											$column = month_lang($v2->bulan).' '.$v2->tahun;
											$column .= '<br>('.$v2->singkatan.')';
											$column .= '<br> <span class="t-column">Biaya Pd Bulan</span>';
											th($column,'','class="text-center align-middle wd-100"');
										}
								tbody();
							table_close();
							?>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>
</div>
<script type="text/javascript">
var status_group = 0;
var controller = '<?= $controller ?>';
$(document).ready(function(){
	resize_window();
	var page_data = $('.page-data').data();
	if(page_data && page_data.status_group == 1){
		status_group = 1;
	}
	if(status_group == 1){
		$('.l-cabang').hide();
		$('#filter_cabang').next(".select2-container").hide();
	}
	getData();
})
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){
	if(status_group == 0){
		getData();
	}
});
$('#filter_cabang_induk').change(function(){
	if(status_group == 1){
		getData();
	}
});
var xhr_ajax = null;
function getData(){
	var cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
	}
	if(!cabang){ return ''; }
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ cabang
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : { status_group : status_group },
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	if(!res.status){
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$('.table-app tbody').html(res.view);
        	cLoader.close();
        	checkSubData();
		}
    });
}
$(document).on('click','.btn-export',function(){
	var cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		cabang = $('#filter_cabang option:selected').val();
	}
	if(!cabang){ return ''; }

	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var post_data = {
        "csrf_token"    	: x[0],
        "export" 			: "export",
        "status_group" 		: status_group,
    }
    var url = base_url + 'transaction/'+controller+'/data';
    url += '/'+ $('#filter_anggaran').val();
    url += '/'+ cabang
    $.redirect(url,post_data,"","_blank");
});
</script>