
$('.btn-sort').click(function(){
	$('#modal-sort .modal-body').html('');
	$.ajax({
		url : base_url + 'settings/m_rkf_kolom/sortable',
		type : 'get',
		dataType : 'json',
		success : function(response) {
			$('#modal-sort .modal-body').html(response.content);
			$('#modal-sort').modal();
			$('ol.sortable').nestedSortable({
				forcePlaceholderSize: true,
				handle: 'div',
				helper:	'clone',
				items: 'li',
				opacity: .6,
				placeholder: 'placeholder',
				revert: 250,
				tabSize: 25,
				tolerance: 'pointer',
				toleranceElement: '> div',
				maxLevels: 4,
				isTree: true,
				expandOnHover: 700,
				isAllowed: function(item, parent, dragItem) {
					var x = true;
					if(dragItem.hasClass('module')) {
						if(typeof parent != 'undefined') x = false;
					} else {
						if(typeof parent == 'undefined') x = false;
						if(x && parent.closest('.module').attr('data-module') != dragItem.attr('data-module')) x = false;
					}
					return x;
				}
			});
		}
	});
});
$('#save-posisi').click(function(e){
	e.preventDefault();
	var serialized = $('ol.sortable').nestedSortable('serialize');
	$.ajax({
		url : base_url + 'settings/m_rkf_kolom/save_sortable',
		type : 'post',
		data : serialized,
		dataType : 'json',
		success : function(response) {
			if(response.status == 'success') {
				cAlert.open(response.message,response.status,'refreshData');
			} else {
				cAlert.open(response.message,response.status);
			}
		}
	});
});
