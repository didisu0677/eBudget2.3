
$( document ).ready(function() {
	resize_window();
    getData();
});
$('#filter_kode_inventaris').on('change',function(){
	getData();
})
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
        "kode_inventaris"   : $('#filter_kode_inventaris option:selected').val(),
        "kode_inventaris_txt"   : $('#filter_kode_inventaris option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/rekap_usulan_aset/export';
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
function getData(){
	var txt 	= $('#filter_kode_inventaris').find('option:selected').text();

	var tahun_anggaran = $('#filter_anggaran option:selected').val();
	var kode_inventaris = $('#filter_kode_inventaris').val();
	kode_inventaris = kode_inventaris.replace(" ", "-");

	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/rekap_usulan_aset/data';
	page 	+= '/'+tahun_anggaran;
	page 	+= '/'+kode_inventaris;

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			$('.table-app tbody').html(response.table);
			$('.table-app .d-header').eq(0).html(response.txt_inventaris);
			$('.table-app .d-header').eq(1).html(response.txt_status);
			$(document).find('.txt_title span').html(txt);
			cLoader.close();
			cek_autocode();
			checkSubData();
			var item_act	= {};
			if($('.table-app tbody .btn-input').length > 0) {
				item_act['edit'] 		= {name : lang.realisasi, icon : "edit"};
			}

			var act_count = 0;
			for (var c in item_act) {
				act_count = act_count + 1;
			}
			if(act_count > 0) {
				$.contextMenu({
			        selector: '.table-app tbody tr', 
			        callback: function(key, options) {
			        	if($(this).find('[data-key="'+key+'"]').length > 0) {
				        	if(typeof $(this).find('[data-key="'+key+'"]').attr('href') != 'undefined') {
				        		window.location = $(this).find('[data-key="'+key+'"]').attr('href');
				        	} else {
					        	$(this).find('[data-key="'+key+'"]').trigger('click');
					        }
					    } 
			        },
			        items: item_act
			    });
			}
		}
	});
}

$('.btn-save').on('click',function(){
	var id_status = $('#id_keterangan_inventaris option:selected').val();
	if(!id_status){
		cAlert.open(lang.tidak_ada_data_yang_dipilih);
		return false;
	}
	var msg 	= lang.anda_yakin_menyetujui;
	cConfirm.open(msg,'save_perubahan');
})
function save_perubahan(){
	var id_status = $('#id_keterangan_inventaris option:selected').val();
	if(!id_status){
		cAlert.open(lang.tidak_ada_data_yang_dipilih);
		return false;
	}
	var kode_anggaran = $('#filter_anggaran option:selected').val();
	var kode_inventaris = $('#filter_kode_inventaris').val();
	kode_inventaris = kode_inventaris.replace(" ", "-");

	var data_post = {
		kode_anggaran : kode_anggaran,
		kode_inventaris : kode_inventaris,
		id_status : id_status,
	}

	$.ajax({
		url : base_url + 'transaction/rekap_usulan_aset/save_perubahan',
		data 	: data_post,
		type : 'post',
		success : function(response) {
			cAlert.open(response.message,response.status,response.load);
		}
	})
}
