<?php 
	foreach($grup[0] as $m0) { 

	$no=0;
	$total = 0;
	$jumlah = 0;

	if($m0->kode != 'E.7'):
		if($m0->kode == 'E.1' && $status_capem):
			$item = '<tr>';
			$item .= '<td></td>';
			$item .= '<td colspan="8"><b class="red">'.strtoupper('Anda Telah merencanakan relokasi '.$cab->struktur_cabang.' dengan biaya perkiraan '.custom_format(view_report($harga_relokasi))).'</b></td>';
			$item .= '</tr>';
			echo $item;
		endif;
    ?>
	<tr>
		<td><?php echo $m0->kode; ?></td>
		<td><?php echo $m0->keterangan; ?></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<?php foreach($produk[$m0->kode] as $m1) { 
		$no++;
		$total += ($m1->harga * $m1->jumlah);
		$jumlah += $m1->jumlah ;

		$bgedit ="";
		$contentedit ="false" ;
		if(($m1->grup !='E.4' && $m1->grup !='E.5') || $m1->kode_inventaris =='') {
			// $bgedit = bgEdit();
			$bgedit = "";
			$contentedit ="true" ;
		}	

		$id = 'keterangan';
		if($akses_ubah) {
			// $bgedit = bgEdit();
			$bgedit = "";
			$contentedit ="true" ;
			$id = 'id' ;
		}else{
			$bgedit ="";
			$contentedit ="false" ;
			$id = 'id' ;
		}


		?> 
		<tr>
			<td><?php echo $m1->kode_inventaris; ?></td>
			<td><?php echo $m1->nama_inventaris; ?></td>
			<td><?php echo $m1->catatan; ?></td>
			
			<?php if(!in_array($m1->grup,['E.4','E.5'])): ?>
			<td style="background: <?php echo $bgedit; ?>"><div style="background: <?php echo $bgedit; ?>" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="<?php echo $contentedit; ?>" class="edit-value text-right" data-name="harga" data-id="<?php echo $m1->id; ?>" data-value="<?= $m1->harga ?>"><?php echo custom_format(view_report($m1->harga)); ?></div></td>
			<?php 
			else: 
				echo '<td class="text-right">'.custom_format(view_report($m1->harga)).'</td>';
			endif;?>

			<td style="background: <?php echo $bgedit; ?>"><div style="background: <?php echo $bgedit; ?>" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="<?php echo $contentedit; ?>" class="edit-value text-right" data-name="jumlah" data-id="<?php echo $m1->$id; ?>" data-value="<?= $m1->jumlah ?>"><?php echo custom_format($m1->jumlah); ?></div></td>

			<td style="background: <?php echo $bgedit; ?>"><div style="background: <?php echo $bgedit; ?>" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="<?php echo $contentedit; ?>" class="edit-value text-right" data-name="bulan" data-id="<?php echo $m1->$id; ?>" data-value="<?= $m1->bulan ?>"><?php echo custom_format($m1->bulan); ?></div></td>

			<th style="background: <?php echo $bgedit; ?>"><div style="background: <?php echo $bgedit; ?>" style="min-height: 10px; width: 50px; overflow: hidden;" class="text-right" data-name="bulan" data-id="<?php echo $m1->$id; ?>" data-value="<?= ($m1->harga * $m1->jumlah) ?>"><?php echo custom_format(view_report($m1->harga * $m1->jumlah)); ?></div></th>

			<?php if($akses_ubah): ?>
			<td class="button">
			<button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="<?php echo $m1->id; ?>" title="<?php echo lang('ubah'); ?>"><i class="fa-edit"></i></button>
			<button type="button" class="btn btn-danger btn-delete" data-key="delete" data-id="<?php echo $m1->id; ?>" title="<?php echo lang('hapus'); ?>"><i class="fa-trash-alt"></i></button>
			<?php else: echo '<td></td>'; endif; ?>
			<!--
			<button type="button" class="btn btn-danger btn-delete" data-key="delete" data-id="<?php echo $m1->id; ?>" title="<?php echo lang('hapus'); ?>"><i class="fa-trash-alt"></i></button>
			-->
			</td>
			<?php
			$key = multidimensional_search($status_inventaris,['kode_inventaris' => $m1->kode_inventaris]);
			$txt_status = '';
			if(strlen($key)>0):
				$txt_status = $status_inventaris[$key]['nama'];
			endif;
			?>
			<td><?= $txt_status ?></td>
		</tr>
	<?php } 
	?>
		<tr>
			<th style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;"></th>
			<th style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;">TOTAL <?php echo $m0->kode; ?></th>
			<th style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;"></th>
			<th style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;"></th>
			<th class="text-right" style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;"><?php echo custom_format($jumlah); ?></th>
			<th style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;"></th>
			<th class="text-right" style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;"><?php echo custom_format(view_report($total)); ?></th>
			<th style="background: #f0f0f0;" style="min-height: 10px; width: 50px; overflow: hidden;"></th>
			<th style="background: #f0f0f0;" style="min-height: 10px; overflow: hidden;"></th>
		</tr>
		<tr>
			<td class="border-none bg-white text-white" colspan="8">.</td>
		</tr>

<?php 
endif;
$t_jumlah = 0;
} ?>
		
	