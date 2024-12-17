<?php
	function select_custom($label,$id,$data,$opt_key,$opt_name,$value=""){
		echo '<label class="mt-2">'.$label.' &nbsp</label>';
		$select = '<select class="select2 custom-select" id="'.$id.'">';
		foreach ($data as $v) {
			$x = explode(',', $opt_name);
			if(count($x)>1):
				$name_txt = remove_spaces($v[$x[0]]).' - '.remove_spaces($v[$x[1]]);
			else:
				$name_txt = remove_spaces($v[$opt_name]);
			endif;
			$selected = '';if($v[$opt_key] == $value): $selected = ' selected'; endif;
			$select .= '<option value="'.$v[$opt_key].'"'.$selected.'>'.$name_txt.'</option>';
		}
		$select .= '</select> &nbsp';
		echo $select;
	}
?>
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
</style>
<div class="content-header">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php
			$arr = [
			    ['btn-export','Export Data','fa-upload'],
			];
			echo ''.access_button('',$arr,true);
			?>
			
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body">
	<div class="d-content"></div>
	<div class="main-container mb-3">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<?= lang('keterangan') ?>
					</div>
					<div class="card-body">
						<div class="row">
							<?php
								foreach ($keterangan as $k => $v) {
									echo '<div class="col-sm-2">
										<img height="12" src="'.$path_file.$v['image'].'"/> '.$v['nama'].'
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
<div class="filter-panel">
	<div class="filter-header">Filter <button type="button" class="filter-close btn-filter-panel-hide">×</button></div>
	<div class="filter-body style-select2">
	<?php
	form_open('','post','form-filter');
		col_init(12,12);
		input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
		select_custom(lang('anggaran'),'filter_tahun',$tahun,'kode_anggaran','keterangan', user('kode_anggaran'));
		select_custom(lang('coa'),'filter_coa',$coa,'coa','coa,name');
		select_custom(lang('bulan'),'filter_bulan',$bulan,'value','name');
	form_close();
	?>
	</div>
</div>
<script type="text/javascript">
var controller = '<?= $controller ?>';
$(document).ready(function () {
	getContent();
});
$('#filter_tahun').change(function(){getContent();});
$('#filter_coa').change(function(){getContent();});
$('#filter_bulan').change(function(){getContent();});
function getContent(){
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/get_content';
	
	var tahun 	= $('#filter_tahun option:selected').val();
	var bulan 	= $('#filter_bulan option:selected').val();
	var coa 	= $('#filter_coa option:selected').val();

	var classnya = 'd-'+bulan+'-'+coa;
	var length = $('body').find('.'+classnya).length;
	var length_body = $('body').find('.d-content-body').length;

	if(length_body>0){
		$('body').find('.d-content-body').hide(300);
	}

	if(length<=0){
		$.ajax({
			url 	: page,
			data 	: {
				tahun 	: tahun,
				bulan 	: bulan,
				coa 	: coa,
			},
			type	: 'post',
			dataType: 'json',
			success	: function(response) {
				cLoader.close();
				if(response.status){
					$('.d-content').append('<div class="d-content-body '+classnya+'"></div>');
					$('body').find('.'+classnya).html(response.view);
					resize_window();
					getData(tahun,bulan,coa);
				}else{
					cAlert.open(response.message,'info');
				}
			}
		});
	}else{
		$('body').find('.'+classnya).show(300);
		cLoader.close();
	}
}
function getData(tahun,bulan,coa){
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/data';
	var classnya = 'd-'+bulan+'-'+coa;
	$.ajax({
		url 	: page,
		data 	: {
			tahun 	: tahun,
			bulan 	: bulan,
			coa 	: coa,
		},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			$('body').find('.'+classnya+' .table-app tbody').html(response.view);
			$('body').find('.'+classnya+' .tbl-total tbody').html(response.total);
			checkSubData2(classnya);
			cLoader.close();
		}
	});
}
function checkSubData2(classnya){
	for (var i = 1; i <= 6; i++) {
		if($(document).find('.'+classnya+' .sb-'+i).length>0){
			var dt = $(document).find('.sb-'+i);
			$.each(dt,function(k,v){
				var text = $(v).text();
				text = text.replaceAll('|-----', "");
				$(v).text('|----- '+text);
			})
		}
	}
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

$('.btn-export').on('click',function(){
	var coa 	 = $('#filter_coa option:selected').val();
	var bulan 	 = $('#filter_bulan option:selected').val();
	var tahun 	 = $('#filter_anggaran option:selected').val();
	var classnya = 'd-'+bulan+'-'+coa;
	var length = $(document).find('.'+classnya).length;
	if(length<=0){
		cAlert.open(lang.data_tidak_ditemukan);
		return false;
	}

	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));

    var classnya = ['.'+classnya+' .t-1','.'+classnya+' .t-2'];
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
        "coa" : coa,
        "coa_txt" : $('#filter_coa option:selected').text(),
        "bulan" : bulan,
        "bulan_txt" : $('#filter_bulan option:selected').text(),
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
                arrayOfThisRowHeader.push(val.trim());
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