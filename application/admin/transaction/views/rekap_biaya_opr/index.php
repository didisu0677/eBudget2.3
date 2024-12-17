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
			<label class=""><?php echo lang('coa'); ?>  &nbsp</label>
			<select class="select2 number-select" id="filter_coa">
				<?php foreach ($coa as $v) { ?>
                <option value="<?= $v->glwnco; ?>"><?= $v->glwnco.' - '.remove_spaces($v->glwdes) ?></option>
                <?php } ?>
			</select>

			<?php
			$arr = [
			    ['btn-export','Export Data','fa-upload'],
			];
			echo ' '.access_button('',$arr);
			?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-12 col-12">
				<div class="card d-content">
		    		<div class="card-header text-center"><?= $title.'</br>'.get_view_report() ?></div>
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
$(document).on('change','#filter_coa',function(){
	loadData();
})
function loadData(){
	var coa = $('#filter_coa option:selected').val();
	if(!coa){ return false; }
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
  	xhr_ajax = $.ajax({
        url: page,
        data : {
        	coa : coa
        },
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
    		resize_window();
    		checkSubData();
    		cLoader.close();
		}
    });
}

$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
  
    var classnya = ['.d-content'];
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
        "coa"   	: $('#filter_coa option:selected').val(),
        "coa_txt"   : $('#filter_coa option:selected').text(),
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
    $(classnya).find(" table tr").each(function() {
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
