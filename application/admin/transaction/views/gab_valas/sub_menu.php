<div class="main-container p-2 bg-grey">
	<div class="tab-app">
		<ul class="nav nav-pills card-header-pills pill-w-badge custom-nav" id="myTab" role="tablist">
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