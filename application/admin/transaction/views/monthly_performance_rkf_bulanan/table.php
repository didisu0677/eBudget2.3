<?php
$item_div = '';
$item  	  = '';
$select = "
	sum(case 
		when c.status = 1 then b.bobot
		when c.status is null and b.status = 1 then b.bobot
		else 0
	end) as bobot_selesai,
	sum(case 
		when c.status in (0,2) then b.bobot
		when c.status is null and b.status in (0,2) then b.bobot
		else 0
	end) as bobot_blm_selesai
";
$arrTotal = [
	'jumlah_div' => 0,
	'jumlah' => 0,
	's_blm_sel' => 0,
	's_proses' => 0,
	's_selesai' => 0,
	'selesai' => 0,
	'blm_selesai' => 0,
];
foreach($divisi as $k => $v){
	$nama_cabang = explode('-',$v['nama_cabang']);
	if(count($nama_cabang)<=1):
		$v['nama_cabang'] = $v['kode_cabang'].' - '.$v['nama_cabang'];
	endif;
	$arrDiv = [
		'jumlah_div' => 0,
		'jumlah' => 0,
		's_blm_sel' => 0,
		's_proses' => 0,
		's_selesai' => 0,
		'selesai' => 0,
		'blm_selesai' => 0,
	];
	if($v['status_group'] == '1'):
		$item_sub_div = '';
		$cab = get_data('tbl_m_cabang a',[
			'select' => '
				a.kode_cabang,a.nama_cabang,'
			,
			'where' => [
				'a.kode_anggaran' 	=> $anggaran->kode_anggaran,
				'a.is_active' 		=> 1,
				'a.parent_id' 		=> $v['id']
			],
		])->result();
		foreach($cab as $k2 => $v2){
			$nama_cabang = explode('-',$v2->nama_cabang);
			if(count($nama_cabang)<=1):
				$v2->nama_cabang = $v2->kode_cabang.' - '.$v2->nama_cabang;
			endif;

			$p_kerja = get_data('tbl_input_rkf a',[
				'select' 	=> 'a.program_kerja,a.status_program_kerja as status,'.$select,
				'join' => [
					"tbl_input_rkf_detail b on a.id = b.id_input_rkf type left",
					"tbl_input_rkf_detail_status c on b.id = c.id_input_rkf_detail and c.bulan = '$bulan' type left"
				],
				'where' 	=> [
					'a.kode_anggaran' => $anggaran->kode_anggaran,
					'a.kode_cabang'   => $v2->kode_cabang,
					'b.bulan <=' 	  => $bulan 
				],
				'group_by'	=> 'a.id',
				'order_by' 	=> 'a.id,b.bulan',
			])->result();

			$item_p_kerja = '';
			$arrSubDiv = [
				'jumlah' => 0,
				's_blm_sel' => 0,
				's_proses' => 0,
				's_selesai' => 0,
				'selesai' => 0,
				'blm_selesai' => 0,
			];
			foreach($p_kerja as $k3 => $v3){
				$selesai 	= $v3->bobot_selesai;
				$blm_selesai= $v3->bobot_blm_selesai;
				$s_blm_sel 	= 0;
				$s_proses 	= 0;
				$s_selesai 	= 0;
				if($v3->status == 1) $s_selesai = 1;
				if($v3->status == 3) $s_proses 	= 1;
				if(in_array($v3->status,[0,2])) $s_blm_sel 	= 1;

				if(in_array($kode_cabang,['All',$v['kode_cabang']])):
					$item_p_kerja .= '<tr>';
					$item_p_kerja .= '<td></td>';
					$item_p_kerja .= '<td class="sb-2">'.$v3->program_kerja.'</td>';
					$item_p_kerja .= '<td class="text-center">-</td>';
					$item_p_kerja .= '<td class="text-center">'.$s_blm_sel.'</td>';
					$item_p_kerja .= '<td class="text-center">'.$s_proses.'</td>';
					$item_p_kerja .= '<td class="text-center">'.$s_selesai.'</td>';
					$item_p_kerja .= '<td class="text-center">'.custom_format($blm_selesai,false,2).'</td>';
					$item_p_kerja .= '<td class="text-center">'.custom_format($selesai,false,2).'</td>';
					$item_p_kerja .= '</tr>';
				endif;

				$arrSubDiv['jumlah'] += 1;
				$arrSubDiv['s_blm_sel'] += checkNumber($s_blm_sel);
				$arrSubDiv['s_proses'] 	+= checkNumber($s_proses);
				$arrSubDiv['s_selesai'] += checkNumber($s_selesai);
				$arrSubDiv['blm_selesai'] += checkNumber($blm_selesai);
				$arrSubDiv['selesai'] += checkNumber($selesai);
			}

			$pembagi = $arrSubDiv['jumlah'];
			$selesai = 0;
			$blm_selesai = 0;
			if($pembagi):
				$selesai = ($arrSubDiv['selesai']/(100*$pembagi))*100;
				$blm_selesai = ($arrSubDiv['blm_selesai']/(100*$pembagi))*100;
			endif;

			if(in_array($kode_cabang,['All',$v['kode_cabang']])):
				$item_sub_div .= '<tr>';
				$item_sub_div .= '<td></td>';
				$item_sub_div .= '<td class="sb-1">'.$v2->nama_cabang.'</td>';
				$item_sub_div .= '<td class="text-center">'.$arrSubDiv['jumlah'].'</td>';
				$item_sub_div .= '<td class="text-center">'.$arrSubDiv['s_blm_sel'].'</td>';
				$item_sub_div .= '<td class="text-center">'.$arrSubDiv['s_proses'].'</td>';
				$item_sub_div .= '<td class="text-center">'.$arrSubDiv['s_selesai'].'</td>';
				$item_sub_div .= '<td class="text-center">'.custom_format($blm_selesai,false,2).'</td>';
				$item_sub_div .= '<td class="text-center">'.custom_format($selesai,false,2).'</td>';
				$item_sub_div .= '</tr>';
				$item_sub_div .= $item_p_kerja;
			endif;

			$arrDiv['jumlah_div'] += 1;
			$arrDiv['jumlah'] += checkNumber($arrSubDiv['jumlah']);
			$arrDiv['s_blm_sel'] += checkNumber($arrSubDiv['s_blm_sel']);
			$arrDiv['s_proses'] += checkNumber($arrSubDiv['s_proses']);
			$arrDiv['s_selesai'] += checkNumber($arrSubDiv['s_selesai']);
			$arrDiv['blm_selesai'] += checkNumber($blm_selesai);
			$arrDiv['selesai'] += checkNumber($selesai);
		}

		$pembagi = $arrDiv['jumlah_div'];
		$selesai = 0;
		$blm_selesai = 0;
		if($pembagi):
			$selesai = ($arrDiv['selesai']/(100*$pembagi))*100;
			$blm_selesai = ($arrDiv['blm_selesai']/(100*$pembagi))*100;
		endif;

		if(in_array($kode_cabang,['All',$v['kode_cabang']])):
			$item .= '<tr>';
			$item .= '<td>'.($k+1).'</td>';
			$item .= '<td><b>'.$v['nama_cabang'].'</b></td>';
			$item .= '<td class="text-center">'.$arrDiv['jumlah'].'</td>';
			$item .= '<td class="text-center">'.$arrDiv['s_blm_sel'].'</td>';
			$item .= '<td class="text-center">'.$arrDiv['s_proses'].'</td>';
			$item .= '<td class="text-center">'.$arrDiv['s_selesai'].'</td>';
			$item .= '<td class="text-center">'.custom_format($blm_selesai,false,2).'</td>';
			$item .= '<td class="text-center">'.custom_format($selesai,false,2).'</td>';
			$item .= '</tr>';
			$item .= $item_sub_div;
			$item .= item_kosong();
		endif;
	else:
		$p_kerja = get_data('tbl_input_rkf a',[
			'select' 	=> 'a.program_kerja,a.status_program_kerja as status,'.$select,
			'join' => [
				"tbl_input_rkf_detail b on a.id = b.id_input_rkf type left",
				"tbl_input_rkf_detail_status c on b.id = c.id_input_rkf_detail and c.bulan = '$bulan' type left"
			],
			'where' 	=> [
				'a.kode_anggaran' => $anggaran->kode_anggaran,
				'a.kode_cabang'   => $v['kode_cabang'],
				'b.bulan <=' 	  => $bulan 
			],
			'group_by'	=> 'a.id',
			'order_by' 	=> 'a.id,b.bulan',
		])->result();
		$item_p_kerja = '';
		foreach($p_kerja as $k3 => $v3){
			$selesai 	= $v3->bobot_selesai;
			$blm_selesai= $v3->bobot_blm_selesai;
			$s_blm_sel 	= 0;
			$s_proses 	= 0;
			$s_selesai 	= 0;
			if($v3->status == 1) $s_selesai = 1;
			if($v3->status == 3) $s_proses 	= 1;
			if(in_array($v3->status,[0,2])) $s_blm_sel 	= 1;

			if(in_array($kode_cabang,['All',$v['kode_cabang']])):
				$item_p_kerja .= '<tr>';
				$item_p_kerja .= '<td></td>';
				$item_p_kerja .= '<td class="sb-1">'.$v3->program_kerja.'</td>';
				$item_p_kerja .= '<td class="text-center">-</td>';
				$item_p_kerja .= '<td class="text-center">'.$s_blm_sel.'</td>';
				$item_p_kerja .= '<td class="text-center">'.$s_proses.'</td>';
				$item_p_kerja .= '<td class="text-center">'.$s_selesai.'</td>';
				$item_p_kerja .= '<td class="text-center">'.custom_format($blm_selesai,false,2).'</td>';
				$item_p_kerja .= '<td class="text-center">'.custom_format($selesai,false,2).'</td>';
				$item_p_kerja .= '</tr>';
			endif;

			$arrDiv['jumlah'] += 1;
			$arrDiv['s_blm_sel'] += checkNumber($s_blm_sel);
			$arrDiv['s_proses'] 	+= checkNumber($s_proses);
			$arrDiv['s_selesai'] += checkNumber($s_selesai);
			$arrDiv['blm_selesai'] += checkNumber($blm_selesai);
			$arrDiv['selesai'] += checkNumber($selesai);
		}

		$arrDiv['jumlah_div'] = $arrDiv['jumlah'];
		$pembagi = $arrDiv['jumlah'];
		$selesai = 0;
		$blm_selesai = 0;
		if($pembagi):
			$selesai = ($arrDiv['selesai']/(100*$pembagi))*100;
			$blm_selesai = ($arrDiv['blm_selesai']/(100*$pembagi))*100;
		endif;

		if(in_array($kode_cabang,['All',$v['kode_cabang']])):
			$item .= '<tr>';
			$item .= '<td>'.($k+1).'</td>';
			$item .= '<td><b>'.$v['nama_cabang'].'</b></td>';
			$item .= '<td class="text-center">'.$arrDiv['jumlah'].'</td>';
			$item .= '<td class="text-center">'.$arrDiv['s_blm_sel'].'</td>';
			$item .= '<td class="text-center">'.$arrDiv['s_proses'].'</td>';
			$item .= '<td class="text-center">'.$arrDiv['s_selesai'].'</td>';
			$item .= '<td class="text-center">'.custom_format($blm_selesai,false,2).'</td>';
			$item .= '<td class="text-center">'.custom_format($selesai,false,2).'</td>';
			$item .= '</tr>';
			$item .= $item_p_kerja;
			$item .= item_kosong();
		endif;
	endif;

	$pembagi = $arrDiv['jumlah_div'];
	$selesai = 0;
	$blm_selesai = 0;
	if($pembagi):
		$selesai = ($arrDiv['selesai']/(100*$pembagi))*100;
		$blm_selesai = ($arrDiv['blm_selesai']/(100*$pembagi))*100;
	endif;

	$item_div .= '<tr>';
	$item_div .= '<td>'.($k+1).'</td>';
	$item_div .= '<td class="sb-1">'.$v['nama_cabang'].'</td>';
	$item_div .= '<td class="text-center">'.$arrDiv['jumlah'].'</td>';
	$item_div .= '<td class="text-center">'.$arrDiv['s_blm_sel'].'</td>';
	$item_div .= '<td class="text-center">'.$arrDiv['s_proses'].'</td>';
	$item_div .= '<td class="text-center">'.$arrDiv['s_selesai'].'</td>';
	$item_div .= '<td class="text-center">'.custom_format($blm_selesai,false,2).'</td>';
	$item_div .= '<td class="text-center">'.custom_format($selesai,false,2).'</td>';
	$item_div .= '</tr>';

	$arrTotal['jumlah_div'] += 1;
	$arrTotal['jumlah'] += checkNumber($arrDiv['jumlah']);
	$arrTotal['s_blm_sel'] += checkNumber($arrDiv['s_blm_sel']);
	$arrTotal['s_proses'] += checkNumber($arrDiv['s_proses']);
	$arrTotal['s_selesai'] += checkNumber($arrDiv['s_selesai']);
	$arrTotal['blm_selesai'] += checkNumber($blm_selesai);
	$arrTotal['selesai'] += checkNumber($selesai);
}

$pembagi = $arrTotal['jumlah_div'];
$selesai = 0;
$blm_selesai = 0;
if($pembagi):
	$selesai = ($arrTotal['selesai']/(100*$pembagi))*100;
	$blm_selesai = ($arrTotal['blm_selesai']/(100*$pembagi))*100;
endif;
$item_tot = '<tr>';
$item_tot .= '<td></td>';
$item_tot .= '<td><b>'.lang('total_keselurugan_prog_kerja').'</b></td>';
$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['jumlah']).'</b></td>';
$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['s_blm_sel']).'</b></td>';
$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['s_proses']).'</b></td>';
$item_tot .= '<td class="text-center"><b>'.custom_format($arrTotal['s_selesai']).'</b></td>';
$item_tot .= '<td class="text-center"><b>'.custom_format($blm_selesai,false,2).'</b></td>';
$item_tot .= '<td class="text-center"><b>'.custom_format($selesai,false,2).'</b></td>';
$item_tot .= '</tr>';

echo $item_tot;
echo $item_div;
echo item_kosong();
echo $item;

function item_kosong(){
	$item = '<tr>';
	$item .= '<td class="border-none bg-white white">.</td>';
	for ($i=1; $i <= 7 ; $i++) { 
		$item .= '<td class="border-none bg-white white"></td>';
	}
	$item .= '</tr>';
	return $item;
}
?>