<?php
	$item = '';
	foreach ($kebijakan_fungsi as $a) {
		$classnya = '';
		if($a->nama == '$$SUBDIV'):
			$classnya = 'd-subdiv';
		else:
			$item .= '<tr>';
			$item .= '<th colspan="'.(8+count($detail_tahun)).'" class="bg-1">'.$a->nama.'</th>';
			$item .= '</tr>';
		endif;
		$no = 0;
		$temp_cabang = '';
		foreach ($list as $k => $v) {
			if($a->id == $v->id_kebijakan_fungsi):
				if($a->nama == '$$SUBDIV' and $temp_cabang != $v->nama_cabang):
					$temp_cabang = $v->nama_cabang;
					$no = 0;
					$item .= '<tr>';
					$item .= '<th colspan="'.(8+count($detail_tahun)).'" class="bg-1">'.$v->nama_cabang.'</th>';
					$item .= '</tr>';
				endif;
				$no ++;

				$grup_txt = '';
				$kode_inventaris_txt = '';
				$coa_txt = '';
				if($v->type == 2):
					$grup_txt = $v->grup.' - '.remove_spaces($v->nama_grup);
					if(in_array($v->grup,['E.4','E.5','E.6'])):
						$kode_inventaris_txt = $v->kode_inventaris;
					endif;
				elseif($v->coa):
					$coa_txt = $v->coa.' - '.remove_spaces($v->glwdes);
				endif;

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

				$item .= '<tr>';
				$item .= '<td>'.$no.'</td>';
				$item .= '<td>'.$v->nama_cabang.'</td>';
				$item .= '<td>'.$v->uraian.'</td>';
				$item .= '<td>'.$arr_type[$v->type].'</td>';
				$item .= '<td>'.$grup_txt.'</td>';
				$item .= '<td>'.$kode_inventaris_txt.'</td>';
				$item .= '<td>'.$coa_txt.'</td>';
				$item .= '<td>'.$kantor_txt.'</td>';
				foreach ($detail_tahun as $v2) {
					$field  = 'B_' . sprintf("%02d", $v2->bulan);
					$val = $v->{$field};
					$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				}
				$item .= '</tr>';
			endif;
		}
	}
	echo $item;
?>