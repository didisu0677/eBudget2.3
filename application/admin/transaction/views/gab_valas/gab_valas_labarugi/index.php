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
<div class="content-header page-data" data-additional="<?= $access_additional ?>">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb($title); ?>
		</div>
		<div class="float-right">
			<?php
			input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
			?>
			<label class=""><?php echo lang('anggaran'); ?>  &nbsp</label>
			<select class="select2 infinity number-select" id="filter_anggaran">
				<?php foreach ($anggaran as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select> 		
 			
 			<label class=""><?php echo lang('coa'); ?>  &nbsp</label>
 			<select class="select2" id="filter_coa">
				<?php foreach ($coa as $v) { ?>
                <option value="<?= $v->glwnco ?>"><?= $v->glwnco.' - '.remove_spaces($v->glwdes) ?></option>
                <?php } ?>
			</select>
    		<?php
    		echo ' ';
			$arr = [
				// ['btn-save','Save Data','fa-save'],
			    ['btn-export','Export Data','fa-upload'],
			    // ['btn-import','Import Data','fa-download'],
			    // ['btn-template','Template Import','fa-reg-file-alt']
			];
			echo access_button('',$arr); 
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
	<?php $this->load->view($path.'sub_menu'); ?>
</div>
<div class="content-body mt-6">
<?php $this->load->view($path.'sub_menu'); ?>
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12 col-12">
				<div class="card">
		    		<div class="card-header text-center"><?= $title ?></div>
					<div class="card-body">
						<div class="table-responsive tab-pane fade active show height-window" id="result1">
						<?php
						table_open('table table-bordered table-app table-1 table-hover');
							thead('sticky-top');
								tr();
									th(get_view_report(),'','colspan="'.(count($detail_tahun)+1).'"');
								tr();
									th(lang('cabang'),'','class="text-center align-middle" style="width:auto;min-width:330px"');
									foreach($detail_tahun as $k => $v){
										$column = month_lang($v->bulan);
										th($column.'<br>('.$v->singkatan.')','','class="text-center" style="min-width:100px"');
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
var controller = '<?= $controller ?>';
$( document ).ready(function() {
	resize_window();
    getData();
});
$('#filter_coa').on('change',function(){
	getData();
})
var xhr_ajax = null;
function getData(){
	var txt 	= $('#filter_coa').find('option:selected').text();

	var tahun_anggaran = $('#filter_anggaran option:selected').val();
	var kode = $('#filter_coa').val();

	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
	
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+tahun_anggaran;
	page 	+= '/'+kode;

	xhr_ajax = $.ajax({
		url 	: page,
		data 	: {
			txt : txt,
		},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			$('.table-app tbody').html(response.table);
			cLoader.close();
			checkSubData();
		}
	});
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
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