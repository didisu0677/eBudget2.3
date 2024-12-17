<style type="text/css">
	red{
		color:red;
	}
	.mw-100{
		min-width: 60px !important;
	}
	.mw-150{
		min-width: 100px !important;
	}
	.mw-250{
		min-width: 330px !important;
	}
	.t-sb-1{
		background-color: #cacaca;
	}
	.r-45{
		transform: rotate(45deg);
	}
	.r-45-{
		transform: rotate(-45deg);
	}
	.mt-6{
		margin-top: 5em;
	}
	.select2-selection__rendered{
		text-align: left !important;
	}
	.content-body .select2-container--default .select2-selection--single{
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
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-6">
				<div class="card">
					<div class="card-header text-center div-filter">Filter Data</div>
					<div class="card-body div-filter">
						<?php
							$tahun = json_encode($tahun); $tahun = json_decode($tahun,true);

							col_init(3,6);
							select2(lang('anggaran'),'filter_anggaran','',$tahun,'kode_anggaran','keterangan',user('kode_anggaran'));
							select2(lang('cabang'),'filter_cabang','',$cabang,'kode_cabang','nama_cabang');
							col_init(3,9);
							inputgroup_open(lang('coa'));
							foreach($coa as $k => $v){
								$checked = ''; if($k == 0) $checked = ' checked';
								echo '<div class="custom-checkbox custom-control custom-control-inline">
								<input class="custom-control-input ck_coa" type="checkbox" id="filter_coa'.$k.'" name="filter_coa[]" value="'.$v.'"'.$checked.'>
								<label class="custom-control-label" for="filter_coa'.$k.'">'.$v.'</label>
								</div>';
							}
							inputgroup_close();
						?>
						<div class="form-group row">
							<div class="col-sm-9 offset-sm-3">
								<button class="btn btn-info btn-search" title="Digunakan untuk mengambil data dari server secara realtime" ><?= lang('pilih') ?></button>
        				<button class="btn btn-info btn-refresh" title="Digunakan untuk mengambil data dari server secara realtime" > Refresh Data </button>
        				<button type="button" class="btn btn-success btn-export" title="Export" > Export Data </button>
        				<button class="btn btn-secondary btn-reset" title="Reset">Reset</button>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<div class="text-right"><a id="btn-filter" href="javascript:;">Sembunyikan Filter</a></div>
					</div>
				</div>
			</div>
		</div>

		<div class="row div-content mt-3">
			
		</div>
	</div>
</div>
<script type="text/javascript">
var controller = '<?= $controller ?>';
$('.btn-search').click(function(){
	var cabang 	 = $('#filter_cabang option:selected').val();
	var classnya = 'd-'+cabang;
	var length = $('.div-content').find('#'+classnya).length;
	if(length>0){
		cLoader.open(lang.memuat_data + '...');
		$('.div-content').find('.d-content').hide();
		$('.div-content').find('#'+classnya).show();
		cLoader.close();
	}else{
		getData();
	}
});
$('.btn-refresh').click(function(){
	getData();
});
var xhr_ajax = null; 
function getData(){
	cLoader.open(lang.memuat_data + '...');
	var cabang 	 = $('#filter_cabang option:selected').val();
	var tahun 	 = $('#filter_anggaran option:selected').val();

	if(!cabang){
		cLoader.close();
		return '';
	}

	var classnya = 'd-'+cabang;
	var page 	 = base_url + 'transaction/'+controller+'/data/'+tahun+'/'+cabang;
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    $('.div-content').find('#'+classnya).remove();
    $('.div-content').find('.d-content').hide();

    var ck_coa = $(".ck_coa:checkbox:checked").map(function(){
      return $(this).val();
    }).get();

	xhr_ajax = $.ajax({
		url 	: page,
		data 	: {
			ck_coa : ck_coa
		},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			cLoader.close();
			if(response.status){
				$('.div-content').append(response.view);
				// $.each(response.rank,function(k,v){
				// 	$('.div-content').find('#'+classnya).find('.'+k).html(v);
				// })
				checkSubData2(classnya);
				resize_window();
			}else{
				cAlert.open(response.message,'info');
			}
		}
	});
}
function checkSubData2(classnya){
	for (var i = 1; i <= 6; i++) {
		if($(document).find('#'+classnya+' .sb-'+i).length>0){
			var dt = $(document).find('.sb-'+i);
			$.each(dt,function(k,v){
				var text = $(v).html();
				text = text.replaceAll('|-----', "");
				$(v).html('|----- '+text);
			})
		}
	}
}
var btn_filter = true;
$('#btn-filter').on('click',function(){
	if(btn_filter){
		btn_filter = false;
		$('.div-filter').hide(300);
		$('#btn-filter').html('Tampilkan Filter');
	}else{
		btn_filter = true;
		$('.div-filter').show(300);
		$('#btn-filter').html('Sembunyikan Filter');
	}
})
$('.btn-reset').on('click',function(){
	$('.ck_coa').prop('checked',false);
	$('#filter_coa0').prop('checked',true);
})
$('.btn-export').on('click',function(){
		var cabang 	 = $('#filter_cabang option:selected').val();
		var tahun 	 = $('#filter_anggaran option:selected').val();
		var classnya = 'd-'+cabang;
		var length = $('.div-content').find('#'+classnya).length;
		if(length<=0){
			cAlert.open(lang.data_tidak_ditemukan);
			return false;
		}

		var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));

    var classnya = ['#'+classnya];
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
        "kode_anggaran" 	: tahun,
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang" : cabang,
        "kode_cabang_txt" : $('#filter_cabang option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/'+controller+'/export';
    $.redirect(url,post_data,"","_blank");
})
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