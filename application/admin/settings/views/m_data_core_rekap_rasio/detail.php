<div class="table-responsive data-general" data-id="<?= encode_id($id) ?>">
	<table class="table table-bordered table-app table-detail table-normal">
		<tr>
			<th><?php echo lang('periode'); ?></th>
			<td><?php echo $periode ?></td>
		</tr>
		<tr>
			<th><?php echo lang('update_terakhir'); ?></th>
			<td><?php echo c_date($create_at); ?></td>
		</tr>
		<tr>
			<th><?php echo lang('import_oleh'); ?></th>
			<td><?php echo $create_at; ?></td>
		</tr>
		
		<?php if($file): ?>
		<tr>
			<th>File</th>
			<td><a href="<?php echo base_url('download/file/'.encode_string(dir_upload($dir).$file)); ?>" class="btn btn-info btn-sm"><i class="fa-download"></i> <?php echo lang('unduh'); ?></a></td>
		</tr>
		<?php endif; ?>
	</table>
</div>