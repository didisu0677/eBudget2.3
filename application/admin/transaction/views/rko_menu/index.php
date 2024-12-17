<style type="text/css">
.cabang-info{
	text-align: right;
}
.cabang-info .title{
	font-size: 20px;
    color: #585757;
    font-weight: 500;
    margin-bottom: 20px;
}
.tbl-1 thead th{
	background: #f7ab0099;
    color: #0a0a0ab5;
    text-align: center;
}
.tbl-1 tfoot th, .tbl-2 tfoot th{
    text-align: center !important;
}
.tbl-2 thead th{
    background: #55545196;
    color: #ffffffe3;
    text-align: center;
}
</style>
<div class="content-header page-data" data-additional="<?= $access_additional ?>">
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
    		<?php 
    			echo filter_cabang_admin($access_additional,$cabang);
				$arr = [
					// ['btn-save','Save Data','fa-save'],
				    ['btn-export','Export Data','fa-upload'],
				    // ['btn-import','Import Data','fa-download'],
				    // ['btn-template','Template Import','fa-reg-file-alt']
				];
				echo access_button('',$arr,true); 
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
</div>
<div class="content-body mt-6">
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12 col-12">
				<div class="card">
		    		<div class="card-header text-center"><h5>Rekap Rencana Kerja Operasional <br><br>Tahun <?= $tahun_anggaran ?></h5></div>
					<div class="card-body">
						<!-- <div>
							Pada hari ...... bulan ......  tahun Dua Ribu Dua Puluh  (...  / 12/2020), telah dilakukan Pembahasan Bersama Seluruh Fungsi dalam Penyusunan Rencana Kerja Operasional (RKO) Tahun 2021 dengan rincian sebagai berikut:
						</div> -->
						<div class="row mt-3">
							<div class="col-sm-3 cabang-info">
								Cabang:
								<div class="title cabang"></div>
								Total Aktivitas:
								<div class="title total-data">0</div>
							</div>
							<div class="col-sm-4 t-pipeline">
								<table id="pipeline" class="table table-striped table-bordered table-hover tbl-1">
									<thead>
										<tr>
											<th>Pipeline Pencapaian Besaran Target</th>
											<th>Aktivitas</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach (menu_tab('rko_pipeline_giro') as $k => $v) {
											$item = '<tr>';
											$item .= '<td>'.$v->nama.'</td>';
											$item .= '<td class="text-center pipeline-'.($k+1).'">0</td>';
											$item .= '</tr>';
											echo $item;
										}
									?>
									</tbody>
									<tfoot>
										<tr>
											<td class="text-center"><b>Jumlah</b></td>
											<td class="total text-center"><b>0</b></td>
										</tr>
									</tfoot>
								</table>

								<!-- <table id="pjk" class="table table-striped table-bordered table-hover tbl-1 d-none">
									<thead>
										<tr>
											<th>Pengembangan Jaringan Kantor</th>
											<th>Aktivitas</th>
										</tr>
									</thead>
									<tbody></tbody>
									<tfoot>
										<tr>
											<th>Jumlah</th>
											<th class="total">0</th>
										</tr>
									</tfoot>
								</table> -->
							</div>
							<div class="col-sm-4 t-pko">
								<table id="pko" class="table table-striped table-bordered table-hover tbl-2">
									<thead>
										<tr>
											<th>Program Kerja Operasional</th>
											<th>Aktivitas</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach (menu_tab('pko_pelayanan') as $k => $v) {
											$item = '<tr>';
											$item .= '<td>'.$v->nama.'</td>';
											$item .= '<td class="text-center pko-'.($k+1).'">0</td>';
											$item .= '</tr>';
											echo $item;
										}
									?>
									</tbody>
									<tfoot>
										<tr>
											<td class="text-center"><b>Jumlah</b></td>
											<td class="total text-center">0</td>
										</tr>
									</tfoot>
								</table>

								<!-- <table id="asset" class="table table-striped table-bordered table-hover tbl-2 d-none">
									<thead>
										<tr>
											<th>Pengadaan Aktiva Tetap & Inventaris</th>
											<th>Aktivitas</th>
										</tr>
									</thead>
									<tbody></tbody>
									<tfoot>
										<tr>
											<th>Jumlah</th>
											<th class="total">0</th>
										</tr>
									</tfoot>
								</table> -->
							</div>
						</div>
					</div>	
				</div>
			</div>
		</div>	
	</div>	
</div>
<script type="text/javascript">
var controller = '<?= $controller ?>';
$(document).ready(function () {
	getData();
});
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){getData();});
function getData() {
	var nama_cabang = $('#filter_cabang option:selected').text();
	$('.cabang-info .cabang').text(nama_cabang);

	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			$.each(response,function(k,v){
				$(k).html(v);
			})
			cLoader.close();
		}
	});
}
$('.btn-export').on('click',function(){
	var cabang 	 = $('#filter_cabang option:selected').val();
	var tahun 	 = $('#filter_anggaran option:selected').val();

	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));

    var classnya = ['.t-pipeline','.t-pko'];
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
        "total_data" 		: $('.total-data').text(),
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