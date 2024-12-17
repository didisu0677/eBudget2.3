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
<div class="content-header page-data">
	<div class="main-container">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php
			input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
			?>
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
							select2(lang('struktur_cabang'),'struktur_cabang','',$arr_struktur,'value','nama');
							col_init(3,4);
							select2(lang('bulan'),'filter_bulan','',$bulan,'value','name',1);
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
								<button class="btn btn-info btn-search" href="javascript:;" title="Digunakan untuk mengambil data dari server secara realtime" ><?= lang('pilih') ?></button>
        				<button class="btn btn-info btn-refresh" href="javascript:;" title="Digunakan untuk mengambil data dari server secara realtime" > Refresh Data </button>
        				<button type="button" class="btn btn-success btn-export" title="Export" > Export Data </button>
        				<button class="btn btn-secondary btn-reset" href="javascript:;" title="Reset">Reset</button>
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
		<div class="row">
			<div class="col-sm-12 col-12 mt-3 mb-3">
				<div class="card">
					<div class="card-header text-center"><?= lang('keterangan') ?></div>
					<div class="card-body">
						<div class="table-responsive tab-pane fade active show">
							<?php
							table_open('table table-striped table-bordered table-app table-hover');
								thead();
									tr();
										th('COA');
										th('');
										th('Pencapaian','','style="min-width:850px"');
										th('Pertumbuhan','','style="min-width:850px"');
								tbody();
								foreach($item as $v){
									$string = htmlentities(remove_spaces($v->glwdes), null, 'utf-8');
									$string = str_replace('&nbsp;','',$string);
									$string = str_replace(' ','',$string);
									$string = str_replace('.','',$string);
									
									tr('d_'.$string.' d-ls-coa');
										th(remove_spaces($v->glwdes));
										td(':','text-center','width="30"');
										$keteranganTxt = '<div class="row">';
										foreach($nilai as $v2){
											if($v2['coa'] == $v->coa){
												$keteranganTxt .= '<div class="col-sm-2">
													<span class="color" style="background-color:'.$v2['warna'].'"></span> 
													<b>" '.$v2['nama'].' "</b> '.$v2['keterangan'].'
												</div>';
											}
										}
										$keteranganTxt .= '</div>';
										td($keteranganTxt);

										$keteranganPertTxt = '<div class="row">';
										foreach($nilai_pert as $v2){
											if($v2['coa'] == $v->coa){
												$keteranganPertTxt .= '<div class="col-sm-2">
													<span class="color" style="background-color:'.$v2['warna'].'"></span> 
													<b>" '.$v2['nama'].' "</b> '.$v2['keterangan'].'
												</div>';
											}
										}
										$keteranganPertTxt .= '</div>';
										td($keteranganPertTxt);
								}
							table_close();
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12 col-12 mt-3 mb-3">
				<div class="card">
					<div class="card-header text-center"><?= lang('keterangan').' Nilai Total' ?></div>
					<div class="card-body">
						<div class="row">
						<?php
							foreach ($nilai_total as $k => $v) {
								echo '<div class="col-sm-2">
									<span class="color" style="background-color:'.$v['warna'].'"></span> 
									<b>" '.$v['nama'].' "</b> '.$v['keterangan'].'
								</div>';
							}
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
$(function(){
	$('#struktur_cabang').val('All').trigger('change');
})
$('.btn-search').click(function(){
	var cabang 	 = $('#filter_cabang option:selected').val();
	var bulan 	 = $('#filter_bulan option:selected').val();
	var classnya = 'd-'+cabang+'-'+bulan;
	var length = $('.div-content').find('#'+classnya).length;
	if(length>0){
		cLoader.open(lang.memuat_data + '...');
		$('.div-content').find('.d-content').hide();
		$('.div-content').find('#'+classnya).show();
		cLoader.close();
		check_coa();
	}else{
		check_coa();
		getData();
	}
});
$('.btn-refresh').click(function(){
	getData();
});

function check_coa(){
	var ck_coa = $(".ck_coa:checkbox:checked").map(function(){
		var val = $(this).val();
		val = val.replace(/&nbsp;/g, '');
		val = val.replace(/ /g, '');
		val = val.replace('.', '');
		val = val.replace(/\u00A0/g, '');
    return val;
  }).get();
  $('.d-ls-coa').hide();
  $.each(ck_coa,function(k,v){
  	if(v == 'All'){
  		$('.d-ls-coa').show();
  	}else{
  		$('.d_'+v).show();
  	}
  })
}

var xhr_ajax = null; 
function getData(){
	cLoader.open(lang.memuat_data + '...');
	var cabang 	 = $('#filter_cabang option:selected').val();
	var bulan 	 = $('#filter_bulan option:selected').val();
	var tahun 	 = $('#filter_anggaran option:selected').val();

	if(!cabang){
		cLoader.close();
		return '';
	}

	var classnya = 'd-'+cabang+'-'+bulan;
	var page 	 = base_url + 'transaction/'+controller+'/data/'+tahun+'/'+cabang+'/'+bulan;
	if( xhr_ajax != null ) {
      xhr_ajax.abort();
      xhr_ajax = null;
  }
  $('.div-content').find('#'+classnya).remove();
  $('.div-content').find('.d-content').hide();

  var ck_coa = $(".ck_coa:checkbox:checked").map(function(){
    return $(this).val();
  }).get();

  if(ck_coa.length<=0){
  	cLoader.close();
  	cAlert.open('Coa Tidak Boleh Kosong');
  	return false;
  }
  var struktur_cabang = $('#struktur_cabang option:selected').val();
  if(!struktur_cabang){
  	struktur_cabang = 'All';
  }

	xhr_ajax = $.ajax({
		url 	: page,
		data 	: {
			ck_coa : ck_coa,
		},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			cLoader.close();
			if(response.status){
				$('.div-content').append(response.view);
			
				$.each(response.rank,function(k,v){
					$('.div-content').find('#'+classnya).find('.'+k).html(v);
				})

				// pengecekan struktur cabang
				$.each($('#struktur_cabang option'),function(k,v){
					var val = $(v).val();
					if(!val){
						val = 'All';
					}
					if(struktur_cabang != 'All' && val != struktur_cabang){
						$(document).find('#'+classnya).find('.'+val).remove();
					}
				});

				if(struktur_cabang != 'All'){
					pengecekan_nourut(classnya);
				}

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
	$('#struktur_cabang').val('All').trigger('change');
})
function pengecekan_nourut(classnya){
	var table = $(document).find('#'+classnya).find('#tbl-data1').find('tbody tr');
	$.each(table,function(k,v){
		$(v).find('td').eq(0).text((k+1));
	})

	table = $(document).find('#'+classnya).find('#tbl-data2').find('tbody tr');
	$.each(table,function(k,v){
		$(v).find('td').eq(0).text((k+1));
	})

	table = $(document).find('#'+classnya).find('#tbl-data3').find('tbody tr');
	$.each(table,function(k,v){
		$(v).find('td').eq(0).text((k+1));
	})
}

$('.btn-export').on('click',function(){
	var currentdate = new Date(); 
	var datetime = currentdate.getDate() + "/"
        + (currentdate.getMonth()+1)  + "/" 
        + currentdate.getFullYear() + " @ "  
        + currentdate.getHours() + ":"  
        + currentdate.getMinutes() + ":" 
        + currentdate.getSeconds();

    var cabang 	 = $('#filter_cabang option:selected').val();
	var bulan 	 = $('#filter_bulan option:selected').val();
	var tahun 	 = $('#filter_anggaran option:selected').val();

	var classnya = 'd-'+cabang+'-'+bulan;

	var table = '';
	table += '<table border="1">';
	table += $(document).find('#'+classnya).html();
	table += '</table>';
	var target = table;
	window.open('data:application/vnd.ms-excel,' + encodeURIComponent(target));
})

</script>