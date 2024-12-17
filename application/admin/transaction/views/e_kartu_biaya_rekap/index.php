<style type="text/css">
	.min-w-100{
		min-width: 100px !important;
	}
	.min-w-80{
		min-width: 80px !important;
		width: 80px !important;
	}
	.min-w-ket{
		min-width: 330px !important;
	}
	.filter-panel .select2-selection--single {
	  height: 100% !important;
	  min-height:30px;
	}
	.filter-panel .select2-selection__rendered{
	  word-wrap: break-word !important;
	  text-overflow: inherit !important;
	  white-space: normal !important;
	}
</style>
<div class="content-header page-data" data-coa_selected="<?= $coa_selected ?>">
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
			<?php
                $arr = [
				    ['btn-export','Export Data','fa-upload'],
				];
				echo ' '.access_button('tambah',$arr);
            ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header text-center">
	    				<?= $title ?>
	    				<br><?= lang('coa') ?> <span id="coa_txt"></span>
	    				<br><span id="bln"></span>	
    				</div>
    				<div class="card-body">
    					<div class="table-responsive tab-pane fade active show height-window" id="result1">
    					<?php 
						table_open('table table-striped table-bordered table-app table-hover',false);
							
						table_close();
						?>
    					</div>
    				</div>
    			</div>
    		</div>
    	</div>
	</div>
</div>
<div class="filter-panel">
	<div class="filter-header">Filter <button type="button" class="filter-close btn-filter-panel-hide">×</button></div>
	<div class="filter-body style-select2">
	<?php
	form_open('','post','form-filter');
		col_init(12,12);
		select2(lang('coa'),'filter_coa','',$coa,'glwnco','glwdes');
		select2('Sub '.lang('coa'),'filter_sub_coa');
		input('date',lang('tanggal_mulai'),'filter_tanggal_mulai','',date("01/m/Y"));
		input('date',lang('tanggal_selesai').' (Max 31 Hari)','filter_tanggal_selesai','',date("d/m/Y"));
		echo '<div class="form-group row" bis_skin_checked="1">
				<div class="col-sm-12 text-right" bis_skin_checked="1">
					<button type="button" class="btn btn-primary btn-search">'.lang('cari').' (Shift+Enter)</button>
					<button type="button" class="btn btn-secondary btn-cancel">'.lang('keluar').' (esc)</button>
				</div>
			</div>';
	form_close();
	?>
	</div>
</div>
<script type="text/javascript">
var controller = '<?= $controller ?>';
var xhr_ajax = null;
var first = true;
$(document).ready(function(){
	var coa_selected = $('.page-data').attr('data-coa_selected');
	if(coa_selected){
		$('#filter_coa').val(coa_selected).trigger('change');
	}
	loadData();
});
$('#filter_anggaran').change(function(){
	loadData();
});
$('#filter_coa').change(function(){
	var val = $(this).val();
	if(val){
		var url = base_url+'transaction/'+controller+'/option_sub_coa';
		cLoader.open(lang.memuat_data + '...');
		$.ajax({
	        url: url,
	        type: 'post',
	        data : {
	        	coa : val,
	        	kode_anggaran : $('#filter_anggaran option:selected').val(),
	        },
	        dataType: 'json',
	        success: function(res){
	      		if(!res.status){
	      			cLoader.close();
	      			$('#filter_sub_coa').html('');
	      			cAlert.open(res.message,'failed');
	      		}
	      		$('#filter_sub_coa').html(res.data);
	      		$('#filter_sub_coa').val(res.selected).trigger('change');
	        	cLoader.close();
			}
	    });
	}
});
$('#filter_sub_coa').change(function(){
	var val = $(this).val();
	var b_first = first;
	if(val){
		first = false;
		if(b_first){
			loadData();
		}
		
	}
});
function loadData(){
	if(first){
		return false;
	}

	var filter_coa = $('#filter_coa option:selected').val();
	if(!filter_coa){
		cAlert.open('coa not found','failed');
		return false;
	}

	var filter_sub_coa = $('#filter_sub_coa option:selected').val();
	var filter_sub_coa_txt = $('#filter_sub_coa option:selected').text();
	if(!filter_sub_coa){
		cAlert.open('sub coa not found','failed');
		return false;
	}

    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
        data: {
        	'kode_anggaran' : $('#filter_anggaran option:selected').val(),
        	'coa' 			: filter_coa,
        	'sub_coa' 		: filter_sub_coa,
        	'start_date' 	: $('#filter_tanggal_mulai').val(),
        	'end_date' 		: $('#filter_tanggal_selesai').val(),
        },
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	$('#coa_txt').html(filter_sub_coa_txt);
        	if(!res.status){
        		$('#result1 table').html('');
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$.each(res.class,function(k,v){
        		$(k).html(v);
        	});
        	resize_window();
        	checkSubData();
        	cLoader.close();
		}
    });
}
// keyup
$(document).on('keyup', function(e) {
  if (e.key == "Escape"){
  	if($('.filter-panel').hasClass('active')){
  		$('.filter-close').click();	
  	}else{
  		$('.btn-filter-panel-show').click();
  	}
  }else if (event.keyCode == 13) {     
		if(event.shiftKey){
			$('.btn-search').click();
		}
  }
});
$('.btn-search').on('click',function(){
	loadData();
})
$('.btn-cancel').on('click',function(){
	$('.filter-close').click();
})
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();

    var classnya = ['#result1'];
    var dt = {};
    $.each(classnya,function(k,v){
    	var dt_table = get_data_table(v);
    	var arr_data = dt_table['arr']
    	var arr_header = dt_table['arr_header'];
    	dt[v] = {
    		header : arr_header,
    		data : arr_data
    	}
    });

    var post_data = {
        "data"        		: JSON.stringify(dt),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "sub_coa" 			: $('#filter_sub_coa option:selected').val(),
        "start_date" 		: $('#filter_tanggal_mulai').val(),
        "end_date" 			: $('#filter_tanggal_selesai').val(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/'+controller+'/export';
    $.redirect(url,post_data,"","_blank");
});
function get_data_table(classnya){
    var arr = [];
    var arr_header = [];
    var no = 0;
    var index_cabang = 0;
    $(classnya+" table tr").each(function() {
        var arrayOfThisRowHeader = [];
        var tableDataHeader = $(this).find('th');
        if (tableDataHeader.length > 0) {
            tableDataHeader.each(function(k,v) {
                var val = $(this).text();
                arrayOfThisRowHeader.push($(this).text());
            });
            arr_header.push(arrayOfThisRowHeader);
        }

        var arrayOfThisRow = [];
        var tableData = $(this).find('td');
        if (tableData.length > 0) {
            tableData.each(function() {
                var val = $(this).text();
                if($(this).hasClass('sb-1')){
                    val = '     '+$(this).text();
                }else if($(this).hasClass('sb-2')){
                    val = '          '+$(this).text();
                }else if($(this).hasClass('sb-3')){
                    val = '               '+$(this).text();
                }else if($(this).hasClass('sb-4')){
                    val = '                    '+$(this).text();
                }else if($(this).hasClass('sb-5')){
                    val = '                         '+$(this).text();
                }else if($(this).hasClass('sb-6')){
                    val = '                              '+$(this).text();
                }
                arrayOfThisRow.push(val); 
            });
            arr.push(arrayOfThisRow);
        }
    });
    return {'arr' : arr, 'arr_header' : arr_header};
}
</script>