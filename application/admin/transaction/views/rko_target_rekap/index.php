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

			<label class=""><?php echo lang('coa'); ?>  &nbsp</label>
			<select class="select2 custom-select" id="filter_coa">
				<?php foreach ($coa as $v) { ?>
                <option value="<?= $v->nama ?>"><?= remove_spaces($v->nama) ?></option>
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
	</div>
</div>
<div class="content-body">
	<div class="main-container">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header text-center"><?= $title ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
	    				<?php
	    				table_open('table table-striped table-bordered table-app',false);
	    					thead('sticky-top');
	    						tr();
	    							th(get_view_report(),'','colspan="15"');
								tr();
									th(lang('no'),'','width="60" class="text-center align-middle"');
									th('Kode Cabang','','style="min-width:60px" class="text-center align-middle"');
									th(lang('cabang'),'','style="min-width:330px" class="text-center align-middle"');
									for ($i=1; $i <= 12 ; $i++) { 
										th(month_lang($i),'','style="min-width:100px" class="text-center align-middle"');
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
<?php
modal_open('modal-import',lang('impor'));
	modal_body();
		form_open(base_url('transaction/'.$controller.'/import'),'post','form-import');
			col_init(3,9);
			fileupload('File Excel','fileimport','required','data-accept="xls|xlsx"');
			form_button(lang('impor'),lang('batal'));
		form_close();
modal_close();
?>
<script>
var controller = '<?= $controller ?>';
$(function(){
	resize_window();
	getData();
})
$('#filter_tahun').change(function(){getData();});
$('#filter_coa').change(function(){getData();});
function getData() {
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();

	$.ajax({
		url 	: page,
		data 	: {
			coa : $('#filter_coa option:selected').val(),
		},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			$('.table-app tbody').html(response.table);
			checkSubData();
			cLoader.close();
		}
	});
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var dt_table = get_data_table('#result1');
    var arr_data = dt_table['arr'];
    var arr_header = dt_table['arr_header'];

    var post_data = {
        "header" 			: JSON.stringify(arr_header),
        "data"        		: JSON.stringify(arr_data),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "coa"   			: $('#filter_coa option:selected').val(),
        "coa_txt"   		: $('#filter_coa option:selected').text(),
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