<ol class="sortable">
	<?php foreach($list as $v) { ?>
		<li id="menuItem_<?= $v->id; ?>" class="module" data-module="<?= $v->id; ?>">
			<div class="sort-item">
				<span class="item-title"><?= lang($v->lang) ?></span>
			</div>
		</li>
	<?php } ?>
</ol>