<style type="text/css">
.w-keterangan{
	width: 400px !important; 
}
.w-jumlah{
	width: 150px !important;
}
.w-val{
	width: 100px !important;
}
.content-header{ height: auto !important; }.content-header .float-right{margin-top: 1rem !important;}.content-header .header-info{ position: relative !important; }.mt-6{ margin-top: 4em;}
</style>
<div class="content-header page-data">
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

			<label class=""><?php echo lang('divisi'); ?>  &nbsp</label>
			<select class="select2 custom-select" id="filter_cabang">
			<?php
			foreach($cabang as $v){
				echo '<option value="'.$v['kode_cabang'].'">'.$v['nama_cabang'].'</option>';
			}
			?>
			</select> 

			<label class=""><?php echo lang('bulan'); ?>  &nbsp</label>
			<select class="select2 custom-select" id="filter_bulan">
				<option value="All"><?= lang('all') ?></option>
				<?php
				for ($i=1; $i <= 12 ; $i++) { 
					echo '<option value="'.$i.'">'.month_lang($i).'</option>';
				}
				?>
			</select>
			<?php 
				$arr = [];
					$arr = [
					    ['btn-export','Export Data','fa-upload'],
					];
				echo access_button('',$arr); 
			?>
    	</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body mt-6">
	<div class="main-container mt-2">
		<div class="col-sm-12">
				<div class="card">
					<div class="card-header"><?= lang('rekap_total_divisi') ?></div>
					<div class="card-body">
						<div class="table-responsive tab-pane fade active show height-window" id="result1">
						<?php
							table_open('table table-striped table-bordered table-app table-hover');
								thead('sticky-top');
									tr();
										th(lang('no'),'text-center align-middle','width="30" rowspan="2"');
										th(lang('keterangan'),'text-center align-middle w-keterangan','rowspan="2"');
										th(lang('jumlah_program_kerja'),'text-center align-middle w-jumlah','rowspan="2"');
										th(lang('status_program_kerja'),'text-center align-middle','colspan="3"');
										th(lang('bobot_sd_bulan'),'text-center align-middle','colspan="2"');
									tr();
										th(lang('belum_selesai'),'text-center w-val');
										th(lang('proses'),'text-center w-val');
										th(lang('selesai'),'text-center w-val');
										th(lang('belum_selesai'),'text-center w-val');
										th(lang('selesai'),'text-center w-val');
								tbody();
							table_close();
						?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="d-content"></div>
	</div>
</div>
<script type="text/javascript">
var controller = '<?= $controller ?>';
$('#filter_anggaran').on('change',function(){ loadData(); })
$('#filter_cabang').on('change',function(){ loadData(); })
$('#filter_bulan').on('change',function(){ loadData(); })
$(function(){
	resize_window();
	loadData();
})
var xhr_ajax = null;
function loadData(){
	var kode_anggaran 	= $('#filter_anggaran option:selected').val();
	var cabang 		  	= $('#filter_cabang option:selected').val();
	var bulan 		  	= $('#filter_bulan option:selected').val();

	if(!cabang){
		return false;
	}

	var data_post = {
		kode_anggaran 	: kode_anggaran,
		kode_cabang 	: cabang,
		bulan 			: bulan
	};

	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/'+controller+'/data';
    xhr_ajax = $.ajax({
		url 	: page,
		data 	: data_post,
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			$('#result1 tbody').html(response.view);
			checkSubData();
			cLoader.close();
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
        "kode_cabang"   : $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "bulan"   : $('#filter_bulan option:selected').val(),
        "bulan_txt"   : $('#filter_bulan option:selected').text(),
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