<?php
	$dt_anggaran = $anggaran;
	$item = '';
	if(count($list)>0):
		$temp_id = '';
		$no 	 = 0;
		foreach($list as $k => $v){
			$noTxt 	 = '';
			$produk = 'Tidak'; if($v->produk == 1) $produk = 'Ya';
     		$anggaran = 'Tidak'; if($v->anggaran == 1) $anggaran = 'Ya';

     		// space
     		if($temp_id != $v->id && $no != 0):
     			$item .= '<tr>';
     			foreach($kolom as $c_kolom){
     				$item .= '<td class="border-none bg-white white">.</td>';
     			}
     			$item .= '</tr>';
     		endif;

			$item .= '<tr>';
			$action_status_program = '';
			$bulan = '';
			if($temp_id != $v->id):
				$temp_id = $v->id;
				$no++;
				$noTxt = $no;

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

				// divisi terkait
				$divisi_terkait = $v->divisi_terkait;
				$s_div = false;
				$divisi = '';
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
						foreach ($ls as $kk => $vv) {
							$divisi .= '- '.$vv->nama_cabang.'<br>';
						}
					endif;
				endif;

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
				if($akses_ubah && $v->kode_cabang == $cabang):
					$action_status_program .= '<button type="button" class="btn btn-info btn-approve" data-page="rkf" data-status="3" data-message="Proses" data-id="'.$v->id.'" title="Proses"><i class="fa fa-clock"></i></button>';

					$action_status_program .= '<button type="button" class="btn btn-success btn-approve" data-page="rkf" data-status="1" data-message="Selesai" data-id="'.$v->id.'" title="Selesai"><i class="fa fa-check"></i></button>';

					$action_status_program .= '<button type="button" class="btn btn-warning btn-approve" data-page="rkf" data-status="2" data-message="Belum Selesai" data-id="'.$v->id.'" title="Belum Selesai"><i class="fa fa-window-close"></i></button>';
				endif;
				$v->status_program_kerja_txt = $arr_status[$v->status_program_kerja];
			else:
				$v->kebijakan_umum = '';
				$v->program_kerja = '';
				$v->perspektif = '';
				$v->status_program = '';
				$v->skala_program = '';
				$v->tujuan = '';
				$v->output = '';
				$v->target_finansial = '';
				$v->status_program_kerja_txt = '';
				$produk = '';
				$anggaran = '';
				$anggaran_bulan = '';
				$anggaran_total = '';
				$divisi = '';
				$d_pic = '';
				$status = '';
			endif;

			
			if($v->bulan):
				$bulan = month_lang($v->bulan);
			endif;
			
			$bobot = '';
			if($v->bobot):
				$bobot = custom_format($v->bobot);
			endif;

			$status = '';
			$b_status = false;
			if(strlen($v->status)>0):
				$b_status = true;
				$status = $arr_status[$v->status];
			endif;

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

			foreach($kolom as $v_kolom){
				if(in_array($v_kolom->lang,['no'])):
					$item .= '<td>'.$noTxt.'</td>';
				elseif(in_array($v_kolom->lang,['kebijakan_umum_direksi'])):
					$item .= '<td>'.$v->kebijakan_umum.'</td>';
				elseif(in_array($v_kolom->lang,['program_kerja'])):
					$item .= '<td>'.$v->program_kerja.'</td>';
				elseif(in_array($v_kolom->lang,['produk_aktivitas_baru'])):
					$item .= '<td>'.$produk.'</td>';
				elseif(in_array($v_kolom->lang,['perspektif'])):
					$item .= '<td>'.$v->perspektif.'</td>';
				elseif(in_array($v_kolom->lang,['status_program'])):
					$item .= '<td>'.$v->status_program.'</td>';
				elseif(in_array($v_kolom->lang,['skala_program'])):
					$item .= '<td>'.$v->skala_program.'</td>';
				elseif(in_array($v_kolom->lang,['tujuan'])):
					$item .= '<td>'.$v->tujuan.'</td>';
				elseif(in_array($v_kolom->lang,['output'])):
					$item .= '<td>'.$v->output.'</td>';
				elseif(in_array($v_kolom->lang,['target_financial'])):
					$item .= '<td>'.$v->target_finansial.'</td>';
				elseif(in_array($v_kolom->lang,['anggaran'])):
					$item .= '<td>'.$anggaran.'</td>';
				elseif(in_array($v_kolom->lang,['anggaran_perbulan'])):
					$item .= '<td>'.$anggaran_bulan.'</td>';
				elseif(in_array($v_kolom->lang,['pos_total_anggaran'])):
					$item .= '<td>'.$anggaran_total.'</td>';
				elseif(in_array($v_kolom->lang,['divisi_terkait'])):
					$item .= '<td>'.$divisi.'</td>';
				elseif(in_array($v_kolom->lang,['pic'])):
					$item .= '<td>'.$d_pic.'</td>';
				elseif(in_array($v_kolom->lang,['status_program_kerja'])):
					$item .= '<td>'.$v->status_program_kerja_txt.'</td>';
				elseif(in_array($v_kolom->lang,['act_status_program_kerja'])):
					$item .= '<td class="button text-center">'.$action_status_program.'</td>';
				elseif(in_array($v_kolom->lang,['bulan'])):
					$item .= '<td>'.$bulan.'</td>';
				elseif(in_array($v_kolom->lang,['uraian'])):
					$item .= '<td>'.$v->uraian.'</td>';
				elseif(in_array($v_kolom->lang,['bobot'])):
					$bobot = '';
					if($v->bobot):
						$bobot = custom_format($v->bobot);
					endif;
					$item .= '<td class="text-right">'.$bobot.'</td>';
				elseif(in_array($v_kolom->lang,['status_progres'])):
					$item .= '<td>'.$status.'</td>';
				elseif(in_array($v_kolom->lang,['act_status_progres'])):
					$item .= '<td class="button text-center">'.$action.'</td>';
				elseif(in_array($v_kolom->lang,['aktivitas_penjelasan'])):
					$item .= '<td><div style="overflow: hidden;" contenteditable="'.$contentedit.'" class="edit-text text-left w-150" data-name="keterangan" data-id="'.$v->detail_id.'" data-value="'.$v->detail_keterangan.'">'.$v->detail_keterangan.'</div></td>';
				elseif(in_array($v_kolom->lang,['keterangan_1'])):
					$item .= '<td><div style="overflow: hidden;" contenteditable="'.$contentedit.'" class="edit-text text-left w-150" data-name="keterangan2" data-id="'.$v->detail_id.'" data-value="'.$v->detail_keterangan2.'">'.$v->detail_keterangan2.'</div></td>';
				elseif(in_array($v_kolom->lang,['keterangan_2'])):
					$item .= '<td><div style="overflow: hidden;" contenteditable="'.$contentedit.'" class="edit-text text-left w-150" data-name="keterangan3" data-id="'.$v->detail_id.'" data-value="'.$v->detail_keterangan3.'">'.$v->detail_keterangan3.'</div></td>';
				endif;
			}

			$item .= '</tr>';
		}
	else:
		$item .= '<tr>';
		$item .= '<th colspan="'.count($kolom).'">'.lang('data_not_found').'</th>';
		$item .= '</tr>';
	endif;
	echo $item;
?>