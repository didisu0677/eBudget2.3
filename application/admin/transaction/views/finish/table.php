<table class="table table-striped table-bordered table-app table-hover">
	<thead class="sticky-top">
		<tr>
			
			<?php 
			$rowspan = 1;
			$item_header = '';
			$item_header2 = '';
			$item = '';
			foreach($menu as $v){
				$nama = '';
				foreach(explode(' ',$v->nama) as $k2 => $v2){
					$nama .= $v2.' ';
					if(($k2 % 2) == 1) $nama .= '</br>';
				}
				$item .= '<th class="text-center align-middle">'.$nama.'</th>';
				$button 	= '';
				$checkbox	= '';
				$disabled 	= ' disabled';
				if($access['access_edit']):
					$disabled = '';
					$rowspan = 3;
					$button .= '<button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="'.$v->id.'" title="'.lang('ubah').'"><i class="fa-edit"></i></button>';
					$checkbox .= '<div class="custom-checkbox custom-control"><input class="custom-control-input ck-all" type="checkbox" id="ck-all-'.$v->id.'" data-id="'.$v->id.'" value="1"'.$disabled.'><label class="custom-control-label" for="ck-all-'.$v->id.'">&nbsp;</label></div>';
				endif;
				if($access['access_delete']):
					$rowspan = 3;
					$button .= '<button type="button" class="btn btn-danger btn-delete" data-key="delete" data-id="'.$v->id.'" title="'.lang('hapus').'"><i class="fa-trash-alt"></i></button>';
				endif;
				if($button) $item_header .= '<th class="button text-center">'.$button.'</th>';
				if($checkbox) $item_header2 .= '<th class="text-center align-middle">'.$checkbox.'</th>';
			} ?>
			<th class="text-center align-middle" width="50" rowspan="<?= $rowspan ?>"><?= lang('no') ?></th>
			<th class="text-center align-middle w-cabang" rowspan="<?= $rowspan ?>"><?= lang('cabang') ?></th>
			<?= $item ?>
		</tr>
		<?php
		if($item_header):
			echo '<tr>';
			echo $item_header;
			echo '</tr>';
		endif;
		if($item_header2):
			echo '<tr>';
			echo $item_header2;
			echo '</tr>';
		endif;
		?>
	</thead>
	<tbody>
	<?php
	$data = [
		'no' 		=> 0,
		'cabang'	=> $cabang,
		'access' 	=> $access,
		'menu' 		=> $menu,
	];
	$item = '';
	foreach($cabang[0] as $cab){
		$data['no'] += 1;
		$item .= '<tr>';
		$item .= '<td>'.($data['no']).'</td>';
		$item .= '<td>'.remove_spaces($cab->nama_cabang).'</td>';

		foreach($menu as $v){
			$menu_cabang = [];
			$checked 	 = '';
			if($v->kode_cabang):
				$menu_cabang = json_decode($v->kode_cabang,true);
				if(in_array($cab->kode_cabang,$menu_cabang,true)) $checked = ' checked';
			endif;
			$id = $cab->id.'-'.$v->id;
			$disabled 	= ' disabled';
			if($access['access_edit']):
				$disabled = '';
			endif;
			$checkbox = '<div class="custom-checkbox custom-control"><input class="custom-control-input d-ck-child" type="checkbox" id="ck-'.$id.'" name="ck['.$v->id.'][]" value="'.$cab->kode_cabang.'" data-id="'.$v->id.'" data-cab_id="'.$id.'"'.$checked.$disabled.'><label class="custom-control-label" for="ck-'.$id.'">&nbsp;</label></div>';
			$item .= '<td class="text-center">'.$checkbox.'</td>';
		}

		$item .= '</tr>';

		$dt_more = more($cab->id,$data,1);
		$data 	 = $dt_more['data'];
		$item 	.= $dt_more['item'];
	}
	echo $item;
	?>
	</tbody>
</table>

<?php
function more($id,$data,$count){
	$cabang = $data['cabang'];
	$access = $data['access'];
	$menu 	= $data['menu'];

	$status = false;
	$item 	= '';
	if(isset($cabang[$id])):
		foreach($cabang[$id] as $cab){
			$data['no'] += 1;
			$item .= '<tr>';
			$item .= '<td>'.($data['no']).'</td>';
			$item .= '<td class="sb-'.$count.'">'.remove_spaces($cab->nama_cabang).'</td>';

			foreach($menu as $v){
				$menu_cabang = [];
				$checked 	 = '';
				if($v->kode_cabang):
					$menu_cabang = json_decode($v->kode_cabang,true);
					if(in_array($cab->kode_cabang,$menu_cabang,true)) $checked = ' checked';
				endif;

				$disabled 	= ' disabled';
				if($access['access_edit']):
					$disabled = '';
				endif;

				$x_id = $cab->id.'-'.$v->id;
				$parent = $id.'-'.$v->id;
				$checkbox = '<div class="custom-checkbox custom-control"><input class="custom-control-input d-ck-child" type="checkbox" id="ck-'.$x_id.'" name="ck['.$v->id.'][]" value="'.$cab->kode_cabang.'" data-id="'.$v->id.'" data-cab_id="'.$x_id.'" data-cab_parent="'.$parent.'"'.$checked.$disabled.'><label class="custom-control-label" for="ck-'.$x_id.'">&nbsp;</label></div>';
				$item .= '<td class="text-center">'.$checkbox.'</td>';
			}

			$item .= '</tr>';

			$dt_more = more($cab->id,$data,($count+1));
			$data 	 = $dt_more['data'];
			$item 	.= $dt_more['item'];
		}
	endif;
	return [
		'item' 	=> $item,
		'data'	=> $data,
	];
}
?>