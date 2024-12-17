
var offset 	= 0;
var limit	= 15;
var busy	= false;
function get_data() {
	if(busy == false) {
		busy	= true;
		$.ajax({
			url		: $('#load-more').attr('action'),
			data	: {'offset':offset,'limit':limit},
			type	: 'post',
			dataType: 'json',
			success	: function(response) {
				$('.notification').append(response.data);
				busy = false;
				if(parseInt(response.num) < limit) {
					$('#btn-more').hide();
				} else {
					offset += parseInt(response.num);
				}
			}
		});
	}
}
$('#btn-more').click(function(e){
	e.preventDefault();
	get_data();
});
$(document).ready(function(){
	get_data();
});
$('.btn-view').click(function(e){
	e.preventDefault();
	$.ajax({
		url		: $('#load-more').attr('action').replace('load_data','is_read'),
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			$('.notification').html('');
			$('.dropdown-notification .tag').remove();
			offset = 0;
			get_data();
		}
	});
});
