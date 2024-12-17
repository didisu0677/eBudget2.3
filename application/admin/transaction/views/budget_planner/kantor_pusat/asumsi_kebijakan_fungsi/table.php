<?php
	$item = '';
	$bgedit ="";
	$contentedit ="false" ;
	$id = 'keterangan';
	if($akses_ubah) {
		$bgedit =bgEdit();
		$contentedit ="true" ;
		$id = 'id' ;
	}
	foreach ($kebijakan_fungsi as $a) {
		$classnya = '';
		if($a->nama == '$$SUBDIV'):
			$classnya = 'd-subdiv';
		endif;
		$item .= '<tr>';
		$item .= '<td class="'.$classnya.'" colspan="'.(9+count($detail_tahun)).'" class="bg-1"><b>'.$a->nama.'</b></td>';
		$item .= '</tr>';
		$no = 0;
		foreach ($list as $k => $v) {
			
			if($a->id == $v->id_kebijakan_fungsi):
				$type_cabang = json_decode($v->type_cabang,true);
				if(!is_array($type_cabang)) $type_cabang = [];
				$kantor_txt = '';
				$no_kantor = 0;
				if(in_array('all',$type_cabang)): $no_kantor++; $kantor_txt .= $no_kantor.'. '.lang('all').',<br>'; endif;
				if(in_array('kc',$type_cabang)): $no_kantor++; $kantor_txt .= $no_kantor.'. KC,<br>'; endif;
				if(in_array('kcp',$type_cabang)): $no_kantor++; $kantor_txt .= $no_kantor.'. KCP,<br>'; endif;
				if(count($type_cabang)>0):
					$ls_cabang = get_data('tbl_m_cabang',[
						'select' => 'nama_cabang',
						'where' => [
							'kode_cabang' => $type_cabang,
							'kode_anggaran' => $v->kode_anggaran,
						]
					])->result();
					foreach ($ls_cabang as $v2) {
						$no_kantor++; $kantor_txt .= $no_kantor.'. '.remove_spaces($v2->nama_cabang).',<br>';
					}
				endif;

				$coa_txt = '';
				$group_txt = '';
				$kode_inventaris_txt = '';
				if($v->type == 1):
					$coa_txt = $v->coa.' - '.remove_spaces($v->glwdes);
				else:
					$group_txt = $v->grup.' - '.remove_spaces($v->nama_grup);
					$kode_inventaris_txt = $v->kode_inventaris;
				endif;

				$no ++;
				$item .= '<tr>';
				$item .= '<td>'.$no.'</td>';
				$item .= '<td class="'.$classnya.'">'.$v->kebijakan_fungsi.'</td>';
				$item .= '<td>'.$v->uraian.'</td>';
				$item .= '<td>'.$arr_type[$v->type].'</td>';
				$item .= '<td>'.$group_txt.'</td>';
				$item .= '<td>'.$kode_inventaris_txt.'</td>';
				$item .= '<td>'.$coa_txt.'</td>';
				$item .= '<td>'.$kantor_txt.'</td>';
				foreach ($detail_tahun as $v2) {
					$field  = 'B_' . sprintf("%02d", $v2->bulan);
					$val = $v->{$field};
					if($akses_ubah):
						$item .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="'.$field.'" data-id="'.$v->id.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
					else:
						$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
					endif;
				}

				$btn_edit = '';
				if($akses_ubah):
					$btn_edit = '<button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="'.$v->id.'" title="'.lang('ubah').'"><i class="fa-edit"></i></button>';
				endif;
				$btn_delete = '';
				if($access_delete):
					$btn_delete = '<button type="button" class="btn btn-danger btn-delete" data-key="delete" data-id="'.$v->id.'" title="'.lang('hapus').'"><i class="fa-trash-alt"></i></button>';
				endif;
				$item .= '<td class="button">'.$btn_edit.$btn_delete.'</td>';
				$item .= '</tr>';
			endif;
		}
	}
	echo $item;
?>