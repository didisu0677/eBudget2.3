<?php 
	$no=0;
	$test = [];
	$a= 1;

	$v = '';
	for ($i = 1; $i <= 12; $i++) { 
		$v = 'total'.sprintf("%02d", $i);
		$$v = 0;
	}	

    ?>
	<?php foreach($produk as $m1) { $no++;
		for ($i = 1; $i <= 12; $i++) { 
			$v_field  = 'K_' . sprintf("%02d", $i);
			$v_total = 'total' . sprintf("%02d", $i);
			$$v_total += $m1->$v_field;	

		}	


		$bgedit ="";
		$id = 'keterangan';
		$btn_delete = '';
		if($akses_ubah) {
			$bgedit = bgEdit();
			$contentedit ="true" ;
			$id = 'id' ;
			$btn_delete = '<button type="button" class="btn btn-danger btn-del" data-id="'.$m1->id.'" title="'.lang('hapus').'"><i class="fa-trash-alt"></i></button>';
		}else{
			$bgedit ="";
			$contentedit ="false" ;
			$id = 'id' ;
		}
		?>
		<tr>
			<td><?php echo $no; ?></td>
			<td><?php echo $m1->nama_kegiatan; ?></td>

			<?php
			for ($a=1;$a <= 12 ;$a++) {
				$v_field  = 'K_' . sprintf("%02d", $a);
				echo '<td style="background: '.$bgedit.';"><div style="background: '.$bgedit.';" style="min-height: 10px; width: 50px; overflow: hidden;" contenteditable="'.$contentedit.'" contenteditable="true" class="edit-value text-right" data-name="'.$v_field.'" data-id="'.$m1->$id.'" data-value="'.view_report($m1->$v_field).'">'.custom_format(view_report($m1->$v_field)).'</div></td>';
			}
			?>

		<td class="button">
			<button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="<?php echo $m1->id; ?>" title="<?php echo lang('ubah'); ?>"><i class="fa-edit"></i></button>
			<?= $btn_delete ?>
		</td>
		</tr>

	<?php } ?>

	<tr>
		<th style="background: #757575;color:#fff;" style="min-height: 10px; width: 50px; overflow: hidden;"></th>
		<th style="background: #757575;color:#fff;" style="min-height: 10px; width: 50px; overflow: hidden;">TOTAL</th>
		<?php
		for ($i = 1; $i <= 12; $i++) { 
			$total = 'total' . sprintf("%02d", $i);			
	
			echo '<th class="text-right" style="background: #757575;color:#fff; min-height: 10px; width: 50px; overflow: hidden;">'.custom_format(view_report($$total)).'</th>';
		}	
		?>	

	</tr>	
	<tr>
		<th style="background: #757575;color:#fff;" style="min-height: 10px; width: 50px; overflow: hidden;"></th>
		<th style="background: #757575;color:#fff;" style="min-height: 10px; width: 50px; overflow: hidden;">TOTAL SAMPAI DENGAN</th>
		<?php
		for ($i = 1; $i <= 12; $i++) { 	

				$test = 'total' . sprintf("%02d", $i);	
				$a =$$test;

				$b +=$a;
				$c = $b;


			echo '<th class="text-right" style="background: #757575;color:#fff; min-height: 10px; width: 50px; overflow: hidden;">'.custom_format(view_report($c)).'</th>';

		}	
		?>	

	</tr>
		
	