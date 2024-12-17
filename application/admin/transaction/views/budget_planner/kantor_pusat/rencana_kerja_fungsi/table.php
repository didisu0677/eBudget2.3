<?php
	if(count($list)>0):
     	$item = '';
     	foreach ($list as $k => $v) {
     		$produk = 'Tidak'; if($v->produk == 1) $produk = 'Ya';
     		$anggaran = 'Tidak'; if($v->anggaran == 1) $anggaran = 'Ya';
			$item .= '<tr>';
			$item .= '<td>'.($k+1).'</td>';
			$item .= '<td>'.$v->kebijakan_umum.'</td>';
			$item .= '<td>'.$v->program_kerja.'</td>';
			$item .= '<td>'.$produk.'</td>';
			$item .= '<td>'.$v->perspektif.'</td>';
			$item .= '<td>'.$v->status_program.'</td>';
			$item .= '<td>'.$v->skala_program.'</td>';
			$item .= '<td>'.$v->tujuan.'</td>';
			$item .= '<td>'.$v->output.'</td>';
			$item .= '<td>'.$v->target_finansial.'</td>';
			$item .= '<td>'.$anggaran.'</td>';

			$divisi_terkait = $v->divisi_terkait;
			$s_div = false;
			if($divisi_terkait):
				$divisi_terkait = json_decode($divisi_terkait,true);
				if(count($divisi_terkait)>0):
					$s_div = true;
					$kode_cabang_divisi = $v->kode_cabang;
			        if($v->level4):
			            $dt_cabang = get_data('tbl_m_cabang','id',$v->parent_id)->row();
			            $kode_cabang_divisi = $dt_cabang->kode_cabang;
			        endif;
			        $divisi_terkait[] = $kode_cabang_divisi;

					$ls = get_data('tbl_m_cabang',[
						'where' => [
							'kode_cabang' => $divisi_terkait,
							'kode_anggaran' => $kode_anggaran
						]
					])->result();
					$divisi = '';
					foreach ($ls as $kk => $vv) {
						$divisi .= '- '.$vv->nama_cabang.'<br>';
					}
					$item .= '<td>'.$divisi.'</td>';
				endif;
			endif;
			if(!$s_div) $item .= '<td></td>';

			// bulan uraian bobot
			$ls_detail = get_data('tbl_input_rkf_detail',[
				'where' 	=> [
					'id_input_rkf' => $v->id,
					'bulan != '	=> 0,
				],
				'order_by'	=> 'bulan'
			])->result();

			$bulan 	= '';
			$uraian = '';
			$bobot 	= '';
			foreach ($ls_detail as $k2 => $v2) {
				$bulan 	.= ($k2+1).'. '.month_lang($v2->bulan).'</br>';
				$uraian .= ($k2+1).'. '.$v2->uraian.'</br>';
				$bobot 	.= ($k2+1).'. '.custom_format($v2->bobot).'</br>';
			}
			$item .= '<td>'.$bulan.'</td>';
			$item .= '<td>'.$uraian.'</td>';
			$item .= '<td>'.$bobot.'</td>';

			$item .= '<td class="button">';
			$item .= '<button type="button" class="btn btn-info btn-detail" data-key="act-detil" data-id="'.$v->id.'" title="'.lang('detil').'"><i class="fa-search"></i></button>';
			if($akses_ubah && $v->kode_cabang == $cabang):
				$item .= '<button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="'.$v->id.'" title="'.lang('ubah').'"><i class="fa-edit"></i></button>';
			endif;
			$item .= '</td>';
			$item .= '</tr>';
		}
		echo $item;
	else:
		echo '<tr><th colspan="12">Data Not Found</th></tr>';
	endif;
?>