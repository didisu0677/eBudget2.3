<?php
	$item = '';
	$total = [];
	foreach ($list as $k => $v) {
		$checkbox = json_decode($v->checkbox,true);

		$pic = json_decode($v->pic);
		if(!is_array($pic)) $pic = [];

		$d_pic = '';
		if(count($pic)>0):
			$dt_pic = get_data('tbl_m_pegawai','id',$pic)->result();
			$no = 0;
			foreach($dt_pic as $k2 => $v2){
				$no++;
				$d_pic .= $no.'. '.$v2->nip.' - '.$v2->nama.'<br>';
			}
		endif;

		$item .= '<tr>';
		$item .= '<td>'.($k+1).'</td>';
		$item .= '<td>'.$v->keterangan.'</td>';
		$item .= '<td>'.$v->contact_type_name.'</td>';
		$item .= '<td>'.$v->tipe_nasabah_name.'</td>';
		$item .= '<td>'.$v->tipe_dana_name.'</td>';
		$item .= '<td>'.$v->nama_cabang.'</td>';
		$item .= '<td>'.$d_pic.'</td>';
		$item .= '<td>'.option_pelaksanaan()[$v->pelaksanaan]['name'].'</td>';
		$item .= '<td>'.$v->nama_target.'</td>';
		foreach ($arrWeekOfMonth['week'] as $k2 => $v2) {
			$d = $arrWeekOfMonth['detail'][$v2];
			$x = explode("-", $d);
			$key = $x[0];
			$bln = $x[1];
			$disabled = '';
			if(!$access_edit){ $disabled = ' disabled'; }
			if(isset($checkbox[$key]) && $checkbox[$key] == 1):
				if(isset($total[$bln])):
					$total[$bln] += checkNumber($v->biaya_sampai);
				else:
					$total[$bln] = checkNumber($v->biaya_sampai);
				endif;
				$item .= '<td><div class="custom-checkbox custom-control">
					<input class="custom-control-input d-checkbox" type="checkbox" id="ck-'.$v->id.'-'.$key.'" value="1" checked'.$disabled.'><label class="custom-control-label" for="ck-'.$v->id.'-'.$key.'">&nbsp;</label>
					</div></td>';
			else:
				$item .= '<td><div class="custom-checkbox custom-control">
					<input class="custom-control-input d-checkbox" type="checkbox" id="ck-'.$v->id.'-'.$key.'" value="1"'.$disabled.'><label class="custom-control-label" for="ck-'.$v->id.'-'.$key.'">&nbsp;</label>
					</div></td>';
			endif;
		}
		$item .= '<td class="button">';
		if($access_edit):
			$item .= '<button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="'.$v->id.'" title="'.lang('ubah').'"><i class="fa-edit"></i></button>';
		endif;
		if($access_delete):
			$item .= '<button type="button" class="btn btn-danger btn-delete" data-key="delete" data-id="'.$v->id.'" title="'.lang('hapus').'"><i class="fa-trash-alt"></i></button>';
		endif;
		$item .= '</td>';
		$item .= '</tr>';
	}
	if(count($list)<=0):
		$item .= '<tr><th colspan="'.(count($arrWeekOfMonth['week'])+10).'">'.lang('data_not_found').'</th></tr>';
	else:
		$item .= '<tr>';
		for ($i=1; $i <= 8 ; $i++) { 
			$item .= '<th></th>';
		}
		$item .= '<th class="text-right">'.lang('jumlah').'</th>';
		foreach ($arrWeekOfMonth['month'] as $k => $v) {
			$val = 0;
			if(isset($total[$k])):
				$val = $total[$k];
			endif;
			$item .= '<th class="text-center align-middle" colspan="'.$v.'">'.custom_format(view_report($val)).'</th>';
		}
		$item .= '</tr>';
	endif;
	echo $item;
?>