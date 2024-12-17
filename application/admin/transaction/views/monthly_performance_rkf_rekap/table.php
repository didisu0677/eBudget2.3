<?php
	$CI = get_instance();
	$item = '';
	$item_div = '';

	$select = "
		count(b.id) as jumlah,
		sum(case when status_program_kerja in (0,2) then 1 else 0 end) as belum_selesai,
		sum(case when status_program_kerja = 3 then 1 else 0 end) as proses,
		sum(case when status_program_kerja = 1 then 1 else 0 end) as selesai,
	";
	$arrTotal = [
		'jumlah' => 0,
		'belum_selesai' => 0,
		'proses' => 0,
		'selesai' => 0,
	];
	foreach($divisi as $k => $v){
		$nama_cabang = explode('-',$v['nama_cabang']);
		if(count($nama_cabang)<=1):
			$v['nama_cabang'] = $v['kode_cabang'].' - '.$v['nama_cabang'];
		endif;
		$arrData = [
			'jumlah' => 0,
			'belum_selesai' => 0,
			'proses' => 0,
			'selesai' => 0,
		];
		if($v['status_group'] == '1'):
			$item_sub_div = '';
			
			$cab = get_data('tbl_m_cabang a',[
				'select' => '
					a.kode_cabang,a.nama_cabang,'.$select
				,
				'join' => [
					'tbl_input_rkf b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = a.kode_anggaran type left'
				],
				'where' => [
					'a.kode_anggaran' 	=> $anggaran->kode_anggaran,
					'a.is_active' 		=> 1,
					'a.parent_id' 		=> $v['id']
				],
				'group_by' => 'a.kode_cabang',
				'order_by' => 'a.urutan'
			])->result();
			foreach($cab as $k2 => $v2){
				$nama_cabang = explode('-',$v2->nama_cabang);
				if(count($nama_cabang)<=1):
					$v2->nama_cabang = $v2->kode_cabang.' - '.$v2->nama_cabang;
				endif;
				$bobot_selesai 		= 0;
				$bobot_blm_selesai 	= 0;
				$bobot_proses 		= 0;
				$v2->jumlah 	= checkNumber($v2->jumlah);
				$v2->belum_selesai 	= checkNumber($v2->belum_selesai);
				$v2->proses 	= checkNumber($v2->proses);
				$v2->selesai 	= checkNumber($v2->selesai);
				if($v2->jumlah>0):
					$bobot_blm_selesai 	= ($v2->belum_selesai/$v2->jumlah)*100;
					$bobot_proses 		= ($v2->proses/$v2->jumlah)*100;
					$bobot_selesai 		= ($v2->selesai/$v2->jumlah)*100;
				endif;

				$item_sub_div .= '<tr>';
				$item_sub_div .= '<td></td>';
				$item_sub_div .= '<td class="sb-1">'.$v2->nama_cabang.'</td>';
				$item_sub_div .= '<td class="text-center">'.$v2->jumlah.'</td>';
				$item_sub_div .= '<td class="text-center">'.$v2->belum_selesai.'</td>';
				$item_sub_div .= '<td class="text-center">'.$v2->proses.'</td>';
				$item_sub_div .= '<td class="text-center">'.$v2->selesai.'</td>';
				$item_sub_div .= '<td class="text-center">'.custom_format($bobot_blm_selesai,false,2).'</td>';
				$item_sub_div .= '<td class="text-center">'.custom_format($bobot_proses,false,2).'</td>';
				$item_sub_div .= '<td class="text-center">'.custom_format($bobot_selesai,false,2).'</td>';
				$item_sub_div .= '</tr>';

				$arrData['jumlah'] += $v2->jumlah;
				$arrData['belum_selesai'] += $v2->belum_selesai;
				$arrData['proses'] += $v2->proses;
				$arrData['selesai'] += $v2->selesai;
				
			}
			$bobot_selesai 		= 0;
			$bobot_blm_selesai 	= 0;
			$bobot_proses 		= 0;
			if($arrData['jumlah']>0):
				$bobot_blm_selesai 	= ($arrData['belum_selesai']/$arrData['jumlah'])*100;
				$bobot_proses 		= ($arrData['proses']/$arrData['jumlah'])*100;
				$bobot_selesai 		= ($arrData['selesai']/$arrData['jumlah'])*100;
			endif;
			if(in_array($kode_cabang,['All',$v['kode_cabang']])):
				$item .= '<tr>';
				$item .= '<td>'.($k+1).'</td>';
				$item .= '<td><b>'.$v['nama_cabang'].'</b></td>';
				$item .= '<td class="text-center">'.$arrData['jumlah'].'</td>';
				$item .= '<td class="text-center">'.$arrData['belum_selesai'].'</td>';
				$item .= '<td class="text-center">'.$arrData['proses'].'</td>';
				$item .= '<td class="text-center">'.$arrData['selesai'].'</td>';
				$item .= '<td class="text-center">'.custom_format($bobot_blm_selesai,false,2).'</td>';
				$item .= '<td class="text-center">'.custom_format($bobot_proses,false,2).'</td>';
				$item .= '<td class="text-center">'.custom_format($bobot_selesai,false,2).'</td>';
				$item .= '</tr>';
				$item .= $item_sub_div;
				$item .= item_kosong();
			endif;
		else:
			$cab = get_data('tbl_m_cabang a',[
				'select' => '
					a.kode_cabang,a.nama_cabang,'.$select
				,
				'join' => [
					'tbl_input_rkf b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = a.kode_anggaran type left'
				],
				'where' => [
					'a.kode_anggaran' 	=> $anggaran->kode_anggaran,
					'a.is_active' 		=> 1,
					'a.kode_cabang' 	=> $v['kode_cabang']
				],
				'group_by' => 'a.kode_cabang',
				'order_by' => 'a.urutan'
			])->row();
			$jumlah 			= 0;
			$blm_selesai 		= 0;
			$proses 			= 0;
			$selesai 			= 0;
			$bobot_blm_selesai 	= 0;
			$bobot_proses 		= 0;
			$bobot_selesai 		= 0;
			if($cab):
				$jumlah 		= $cab->jumlah;
				$blm_selesai 	= $cab->belum_selesai;
				$proses 		= $cab->proses;
				$selesai 		= $cab->selesai;
				if($jumlah>0):
					$bobot_blm_selesai 	= (checkNumber($blm_selesai)/checkNumber($jumlah))*100;
					$bobot_proses 		= (checkNumber($proses)/checkNumber($jumlah))*100;
					$bobot_selesai 		= (checkNumber($selesai)/checkNumber($jumlah))*100;
				endif;
			endif;

			if(in_array($kode_cabang,['All',$v['kode_cabang']])):
				$item .= '<tr>';
				$item .= '<td>'.($k+1).'</td>';
				$item .= '<td><b>'.$v['nama_cabang'].'</b></td>';
				$item .= '<td class="text-center">'.$jumlah.'</td>';
				$item .= '<td class="text-center">'.$blm_selesai.'</td>';
				$item .= '<td class="text-center">'.$proses.'</td>';
				$item .= '<td class="text-center">'.$selesai.'</td>';
				$item .= '<td class="text-center">'.$bobot_blm_selesai.'</td>';
				$item .= '<td class="text-center">'.$bobot_proses.'</td>';
				$item .= '<td class="text-center">'.$bobot_selesai.'</td>';
				$item .= '</tr>';
				$item .= item_kosong();
			endif;

			$arrData['jumlah'] = $jumlah;
			$arrData['belum_selesai'] = $blm_selesai;
			$arrData['proses'] += $proses;
			$arrData['selesai'] += $selesai;
		endif;

		$bobot_selesai 		= 0;
		$bobot_blm_selesai 	= 0;
		$bobot_proses 		= 0;
		if($arrData['jumlah']>0):
			$bobot_blm_selesai 	= ($arrData['belum_selesai']/$arrData['jumlah'])*100;
			$bobot_proses 		= ($arrData['proses']/$arrData['jumlah'])*100;
			$bobot_selesai 		= ($arrData['selesai']/$arrData['jumlah'])*100;
		endif;
		$item_div .= '<tr>';
		$item_div .= '<td>'.($k+1).'</td>';
		$item_div .= '<td class="sb-1">'.$v['nama_cabang'].'</td>';
		$item_div .= '<td class="text-center">'.$arrData['jumlah'].'</td>';
		$item_div .= '<td class="text-center">'.$arrData['belum_selesai'].'</td>';
		$item_div .= '<td class="text-center">'.$arrData['proses'].'</td>';
		$item_div .= '<td class="text-center">'.$arrData['selesai'].'</td>';
		$item_div .= '<td class="text-center">'.custom_format($bobot_blm_selesai,false,2).'</td>';
		$item_div .= '<td class="text-center">'.custom_format($bobot_proses,false,2).'</td>';
		$item_div .= '<td class="text-center">'.custom_format($bobot_selesai,false,2).'</td>';
		$item_div .= '</tr>';

		$arrTotal['jumlah'] += $arrData['jumlah'];
		$arrTotal['belum_selesai'] += $arrData['belum_selesai'];
		$arrTotal['proses'] += $arrData['proses'];
		$arrTotal['selesai'] += $arrData['selesai'];
	}

	$bobot_selesai 		= 0;
	$bobot_blm_selesai 	= 0;
	$bobot_proses 		= 0;
	if($arrTotal['jumlah']>0):
		$bobot_blm_selesai 	= ($arrTotal['belum_selesai']/$arrTotal['jumlah'])*100;
		$bobot_proses 		= ($arrTotal['proses']/$arrTotal['jumlah'])*100;
		$bobot_selesai 		= ($arrTotal['selesai']/$arrTotal['jumlah'])*100;
	endif;

	$item_tot = '<tr>';
	$item_tot .= '<td></td>';
	$item_tot .= '<td><b>'.lang('total_keselurugan_prog_kerja').'</b></td>';
	$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['jumlah']).'</b></td>';
	$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['belum_selesai']).'</b></td>';
	$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['proses']).'</b></td>';
	$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['selesai']).'</b></td>';
	$item_tot .= '<td class="text-center"><b>'.custom_format($bobot_blm_selesai,false,2).'</b></td>';
	$item_tot .= '<td class="text-center"><b>'.custom_format($bobot_proses,false,2).'</b></td>';
	$item_tot .= '<td class="text-center"><b>'.custom_format($bobot_selesai,false,2).'</b></td>';
	$item_tot .= '</tr>';

	echo $item_tot;
	echo $item_div;
	echo item_kosong();
	echo $item;

	function item_kosong(){
		$item = '<tr>';
		$item .= '<td class="border-none bg-white white">.</td>';
		for ($i=1; $i <= 8 ; $i++) { 
			$item .= '<td class="border-none bg-white white"></td>';
		}
		$item .= '</tr>';
		return $item;
	}
?>