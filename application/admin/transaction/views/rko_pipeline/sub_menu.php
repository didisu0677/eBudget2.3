<style type="text/css">
.custom-nav li{
	max-width: 100% !important;
}
</style>
<div class="main-container p-2 bg-grey">
	<div class="tab-app">
		<ul class="nav nav-pills card-header-pills pill-w-badge custom-nav" role="tablist">
			<?php
			foreach (menu_tab() as $v) {
				$link = base_url('transaction/'.$v->target);
				$segment = $cur_segment = uri_segment(2) ? uri_segment(2) : uri_segment(1);
				$active = '';
				if($v->target == $segment): $active = ' active'; endif;
				echo '<li class="nav-item">
					<a class="h-100-per nav-link'.$active.'" id="'.$v->id.'" href="'.$link.'"><center>'.$v->nama.'</center></a>
				</li>';
			}
			?>
		</ul>
		
	</div>
</div>

<script type="text/javascript">
var dt_target = ``;
$(document).ready(function () {
	getFinansial();
});
var xhr_ajax2 = null;
function getFinansial(){
	if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }
	xhr_ajax2 = $.ajax({
		url : base_url + 'api/target_finansial_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			xhr_ajax2 = null;
			dt_target = response.data;
		}
	});
}
</script>