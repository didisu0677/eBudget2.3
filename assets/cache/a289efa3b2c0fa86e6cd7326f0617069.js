
var controller = 'monthly_performance_operasional';
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

