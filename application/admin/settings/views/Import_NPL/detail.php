<div class="table-responsive data-general" data-id="<?= encode_id($id) ?>">
	<table class="table table-bordered table-app table-detail table-normal">
		<tr>
			<th><?php echo lang('kode_anggaran'); ?></th>
			<td><?php echo $kode_anggaran; ?></td>
		</tr>
		<tr>
			<th><?php echo lang('keterangan'); ?></th>
			<td><?php echo $keterangan_anggaran; ?></td>
		</tr>
		<tr>
			<th><?php echo lang('tanggal_import'); ?></th>
			<td><?php echo c_date($create_at); ?></td>
		</tr>
		<tr>
			<th><?php echo lang('import_oleh'); ?></th>
			<td><?php echo $create_by; ?></td>
		</tr>
		<?php if($file): ?>
		<tr>
			<th>File</th>
			<td><a href="<?php echo base_url('download/file/'.encode_string(dir_upload('import_npl').$file)); ?>" class="btn btn-info btn-sm"><i class="fa-download"></i> <?php echo lang('unduh'); ?></a></td>
		</tr>
		<?php endif; ?>
	</table>
</div>