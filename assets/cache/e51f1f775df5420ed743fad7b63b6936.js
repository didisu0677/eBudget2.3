
var controller = 'rko_target_rekap';
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
