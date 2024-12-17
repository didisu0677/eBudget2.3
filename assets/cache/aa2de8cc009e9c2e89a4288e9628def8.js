
$('.btn-import').click(function(){
	$('#form-import')[0].reset();
    $('#modal-import .alert').hide();
    $('#modal-import').modal('show');
    $('.fileupload-preview').html('');
});
$(document).on('click','.btn-detail',function(){
	$.get(base_url + 'settings/import_npl/detail/' + $(this).attr('data-id'),function(res){
		cInfo.open(lang.detil,res,{modal_lg:true});
	});
});
