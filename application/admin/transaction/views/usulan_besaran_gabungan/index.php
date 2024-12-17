<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php
			input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
			?>
			<label class=""><?php echo lang('anggaran'); ?>  &nbsp</label>
			<select class="select2 infinity custom-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select>

			<label class=""><?php echo lang('cabang'); ?>  &nbsp</label>
			<select class="select2 custom-select" id="filter_cabang">
				<?php foreach ($cabang as $cab) { ?>
                <option value="<?php echo $cab->kode_cabang; ?>"><?php echo remove_spaces($cab->nama_cabang) ?></option>
                <?php } ?>
			</select>
    		<?php
			$arr = [
			    ['btn-export','Export Data','fa-upload'],
			];
			echo access_button('',$arr);
			?>
    		</div>
		<div class="clearfix"></div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body mt-6">
	<div class="main-container mt-2 div-content">

	</div>
</div>
<script type="text/javascript">
var controller = '<?= $controller ?>';
$(function(){
	getData();
})
$('#filter_tahun').change(function(){
	getData();
});

$('#filter_cabang').change(function(){
	getData();
});
var xhr_ajax = null;
function getData(){
	var cabang = $('#filter_cabang').val();
	if(!cabang){
		return '';
	}

	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

    $('.div-content').html();
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();
	xhr_ajax = $.ajax({
		url 	: page,
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			if(!response.status){
				cLoader.close();
				cAlert.open(response.message,'failed');
				$('.div-content').html('');
				return false;
			}
			$('.div-content').html(response.view);
			cLoader.close();
			$(document).find('.dt_child').hide();
			$.each(response.coa_show,function(k,v){
				$(document).find('.dt_'+v).show();
			})
		}
	});
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
    var dt_table = get_data_table('.div-content');
    var arr_data = dt_table['arr'];
    var arr_header = dt_table['arr_header'];

    var post_data = {
        "header" 			: JSON.stringify(arr_header),
        "data"        		: JSON.stringify(arr_data),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
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
    var count = $(classnya).find('table').length;
    for (var i = 0; i < count; i++) {
    	var title = $(classnya).find('.card-header').eq(i).text();
    	var arrayOfThisRow = [];
    	arrayOfThisRow.push('');
    	arrayOfThisRow.push(title);
    	for (var ii = 1; ii <= 12; ii++) {
    		arrayOfThisRow.push('-');
    	}
    	arr.push(arrayOfThisRow);

    	$(classnya).find('table').eq(i).find('tr').each(function() {
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
	    var arrayOfThisRow = [];
    	arrayOfThisRow.push('');
    	arrayOfThisRow.push('');
    	for (var ii = 1; ii <= 12; ii++) {
    		arrayOfThisRow.push('-');
    	}
    	arr.push(arrayOfThisRow);
    }
    return {'arr' : arr, 'arr_header' : arr_header};
}
</script>
