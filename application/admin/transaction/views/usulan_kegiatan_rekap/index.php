<style type="text/css">
	.bg-c1{
		background-color: #ababab;
	}
	.bg-c2{
		background-color: #d0d0d0;
	}
	.bg-c3{
		background-color: #f5f5f5;
	}
	tr:hover td{
		background-color: #efffc2 !important;
	}
</style>
<div class="content-header">
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
	    				<div class="table-responsive tab-pane fade active show height-window" id="tbl-data1">
	    				<?php
						table_open('table table-bordered table-app table-hover');
							thead('sticky-top');
								tr();
									th(get_view_report(1),'','width="60" colspan="'.(14).'" class="text-left"');
								tr();
									th(lang('kode_cabang'),'','class="text-center align-middle" style="min-width:80px"');
									th(lang('cabang'),'','class="text-center align-middle" style="min-width:315px;width:315px"');
									for ($i=1; $i <= 12 ; $i++) { 
									th(month_lang($i).' '.$tahun->tahun_anggaran.'<br> PD BULAN','text-center','style="min-width:100px;width:100px"');		
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
$( document ).ready(function() {
	resize_window();
    getData();
});
function getData(){
	var tahun_anggaran = $('#filter_anggaran option:selected').val();

	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/usulan_kegiatan_rekap/data';
	page 	+= '/'+tahun_anggaran;

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			$('#tbl-data1 tbody').html(response.table);
			cLoader.close();
			fixedTable();
			checkSubData();
		}
	});
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var dt_table = get_data_table('#tbl-data1');
    var arr_data = dt_table['arr'];
    var arr_header = dt_table['arr_header'];

    var post_data = {
        "header" 			: JSON.stringify(arr_header),
        "data"        		: JSON.stringify(arr_data),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/usulan_kegiatan_rekap/export';
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