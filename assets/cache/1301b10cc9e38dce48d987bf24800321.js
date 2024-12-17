
$('#coa').on('change',function(){
	var txt = $(this).find('option:selected').text();
	txt = txt.split(' - ');
	$('#nama').val(txt[1]);
})
function formOpen(){
	$('#clone_penc').prop('checked',false).trigger('change');
	$('#clone_pert').prop('checked',false).trigger('change');
}
$('#clone_penc').on('change',function(){
	var val  	= $(this).is(':checked');
	var index 	= $('#coa_clone_penc').closest('.form-group');
	index.find('.error').empty();
	index.find('span').removeClass('error');
	if(val){
		$('#coa_clone_penc').attr('data-validation','required');
		index.show();
	}else{
		$('#coa_clone_penc').removeAttr('data-validation');
		index.hide();
	}
})
$('#clone_pert').on('change',function(){
	var val  	= $(this).is(':checked');
	var index 	= $('#coa_clone_pert').closest('.form-group');
	index.find('.error').empty();
	index.find('span').removeClass('error');
	if(val){
		$('#coa_clone_pert').attr('data-validation','required');
		index.show();
	}else{
		$('#coa_clone_pert').removeAttr('data-validation');
		index.hide();
	}
})
