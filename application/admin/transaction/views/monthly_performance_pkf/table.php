<?php
	$dt_anggaran = $anggaran;
	$item = '';
	if(count($list)>0):
		$temp_id = '';
		$no 	 = 0;	
		foreach($list as $k => $v){
			$produk = 'Tidak'; if($v->produk == 1) $produk = 'Ya';
     		$anggaran = 'Tidak'; if($v->anggaran == 1) $anggaran = 'Ya';

			$item .= '<tr>';
			if($temp_id != $v->id):
				$temp_id = $v->id;
				$no++;
				$item .= '<td>'.($no).'</td>';
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

				$anggaran_bulan = '';
				$anggaran_total = '';
				if($v->anggaran == 1):
					$total = 0;
					for ($i=1; $i <= 12 ; $i++) { 
						$field = 'T_'.sprintf("%02d", $i);
						if($v->{$field}):
							$anggaran_bulan .= month_lang($i).' = '.custom_format(view_report($v->{$field})).' </br>';
							$total += $v->{$field};
						endif;
					}
					$anggaran_total .= 'POS : '.$v->coa.'-'.remove_spaces($v->glwdes).'</br>';
					$anggaran_total .= 'Total : '.custom_format(view_report($total));
				endif;

				$item .= '<td>'.$anggaran_bulan.'</td>';
				$item .= '<td>'.$anggaran_total.'</td>';

				// divisi terkait
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
								'kode_anggaran' => $dt_anggaran->kode_anggaran
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

				// PIC
				$d_pic = '';
				if($v->pic):
					$pic = json_decode($v->pic,true);
					if(count($pic)>0):
						$ls = get_data('tbl_m_pegawai','id',$pic)->result();
						foreach ($ls as $kk => $vv) {
							$d_pic .= '- '.$vv->nama.' ('.$vv->nip.')'.'<br>';
						}
					endif;
				endif;
				$item .= '<td>'.$d_pic.'</td>';
				$item .= '<td>'.$arr_status[$v->status_program_kerja].'</td>';
				$action = '';
				if($akses_ubah && $v->kode_cabang == $cabang):
					$action .= '<button type="button" class="btn btn-info btn-approve" data-page="rkf" data-status="3" data-message="Proses" data-id="'.$v->id.'" title="Proses"><i class="fa fa-clock"></i></button>';

					$action .= '<button type="button" class="btn btn-success btn-approve" data-page="rkf" data-status="1" data-message="Selesai" data-id="'.$v->id.'" title="Selesai"><i class="fa fa-check"></i></button>';

					$action .= '<button type="button" class="btn btn-warning btn-approve" data-page="rkf" data-status="2" data-message="Belum Selesai" data-id="'.$v->id.'" title="Belum Selesai"><i class="fa fa-window-close"></i></button>';
				endif;
				$item .= '<td class="button">'.$action.'</td>';
			else:
				for ($i=1; $i <= 17 ; $i++) { 
					$item .= '<td></td>';
				}
			endif;

			$bulan = '';
			if($v->bulan):
				$bulan = month_lang($v->bulan);
			endif;
			$item .= '<td>'.$bulan.'</td>';
			$item .= '<td>'.$v->uraian.'</td>';
			$bobot = '';
			if($v->bobot):
				$bobot = custom_format($v->bobot);
			endif;
			$item .= '<td class="text-right">'.$bobot.'</td>';

			$status = '';
			$b_status = false;
			if(strlen($v->status)>0):
				$b_status = true;
				$status = $arr_status[$v->status];
			endif;
			$item .= '<td>'.$status.'</td>';

			// action
			$action = '';
			$bgedit ="";
			$contentedit ="false" ;
			$id = 'keterangan';
			$action .= '<button type="button" class="btn btn-success btn-file" data-id="'.$v->detail_id.'" title="File/Document"><i class="fa fa-download"></i></button>';
			if($akses_ubah && $v->kode_cabang == $cabang && $b_status):
				// $action .= '<button type="button" class="btn btn-info btn-approve" data-page="detail" data-status="3" data-message="Proses" data-id="'.$v->detail_id.'" title="Proses"><i class="fa fa-clock"></i></button>';

				$action .= '<button type="button" class="btn btn-success btn-approve" data-page="detail" data-status="1" data-message="Selesai" data-id="'.$v->detail_id.'" title="Selesai"><i class="fa fa-check"></i></button>';

				$action .= '<button type="button" class="btn btn-warning btn-approve" data-page="detail" data-status="2" data-message="Belum Selesai" data-id="'.$v->detail_id.'" title="Belum Selesai"><i class="fa fa-window-close"></i></button>';

				$bgedit ="";
				$contentedit ="true" ;
				$id = 'id' ;
				if(!$v->detail_keterangan):
					$v->detail_keterangan = '.........................';
				endif;
				if(!$v->detail_keterangan2):
					$v->detail_keterangan2 = '.........................';
				endif;
				if(!$v->detail_keterangan3):
					$v->detail_keterangan3 = '.........................';
				endif;
			endif;
			$item .= '<td class="button">'.$action.'</td>';
			$item .= '<td><div style="overflow: hidden;" contenteditable="'.$contentedit.'" class="edit-text text-left w-150" data-name="keterangan" data-id="'.$v->detail_id.'" data-value="'.$v->detail_keterangan.'">'.$v->detail_keterangan.'</div></td>';
			$item .= '<td><div style="overflow: hidden;" contenteditable="'.$contentedit.'" class="edit-text text-left w-150" data-name="keterangan2" data-id="'.$v->detail_id.'" data-value="'.$v->detail_keterangan2.'">'.$v->detail_keterangan2.'</div></td>';
			$item .= '<td><div style="overflow: hidden;" contenteditable="'.$contentedit.'" class="edit-text text-left w-150" data-name="keterangan3" data-id="'.$v->detail_id.'" data-value="'.$v->detail_keterangan3.'">'.$v->detail_keterangan3.'</div></td>';

			$item .= '</tr>';
		}
	else:
		$item .= '<tr>';
		$item .= '<th colspan="22">'.lang('data_not_found').'</th>';
		$item .= '</tr>';
	endif;
	echo $item;
?>