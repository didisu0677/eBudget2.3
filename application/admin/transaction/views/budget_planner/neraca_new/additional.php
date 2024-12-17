<?php
	$item = '';

	$item2 = '';
	$arrAssetTotal = [];
	foreach ($arrAdditional as $addit) {
		$key_addit      = 'additional_'.$addit['id'];
		$item2 .= '<tr>';
		$item2 .= '<td></td>';
		$item2 .= '<td></td>';
		$item2 .= '<td></td>';
		$item2 .= '<td><strong>'.$addit['nama'].'</strong></td>';
		$arrSaved = [
			'coa' 		=> $addit['glwnco'],
			'status'	=> false,
		];
		for ($i=1; $i <= 12 ; $i++) { 
			$field  = 'B_' . sprintf("%02d", $i);
			$val 	= round_value(${$key_addit}[$field]);
			$item2 .= '<td class="text-right"><strong>'.check_value($val,true).'</strong></td>';
			if($val):
				$arrSaved['status'] = true;
				$arrSaved[$field] = $val;
			endif;
			if(!isset($arrAssetTotal[$field])):
				$arrAssetTotal[$field] = $val;
			else:
				if($arrAssetTotal[$field] < $val):
					$arrAssetTotal[$field] = $val;
				endif;
			endif;

		}
		$item2 .= '<td class="border-none bg-white"></td>';
		$item2 .= '<td></td>';
		$item2 .= '</tr>';
		checkSaved($anggaran,$cabang,$arrSaved);
	}

	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td></td>';
	$item .= '<td></td>';
	$item .= '<td><strong>ASSET NETTO CABANG</strong></td>';
	$arrSaved = [
		'coa' 		=> '39999991',
		'status'	=> false,
	];
	for ($i=1; $i <= 12 ; $i++) { 
		$field  = 'B_' . sprintf("%02d", $i);
		$val 	= round_value($arrAssetTotal[$field]);
		$item .= '<td class="text-right"><strong>'.check_value($val,true).'</strong></td>';
		if($val):
			$arrSaved['status'] = true;
			$arrSaved[$field] = $val;
		endif;
	}
	$item .= '<td class="border-none bg-white"></td>';
	$item .= '<td></td>';
	$item .= '</tr>';
	checkSaved($anggaran,$cabang,$arrSaved);

	$item .= $item2;
	echo $item;

	function checkSaved($anggaran,$kode_cabang,$data){
		$status = $data['status'];
		unset($data['status']);
		$where = [
			'kode_cabang' => $kode_cabang,
			'kode_anggaran' => $anggaran->kode_anggaran,
			'coa' => $data['coa'],
		];

		$ck = get_data('tbl_budget_plan_neraca',['select' => 'id', 'where' => $where])->row();
		if($ck):
			$data['update_at'] = date("Y-m-d H:i:s");
			$data['update_by'] = user('username');
			update_data('tbl_budget_plan_neraca',$data,'id',$ck->id);
		elseif($status):
			$data['kode_cabang'] = $kode_cabang;
			$data['kode_anggaran'] = $anggaran->kode_anggaran;
			$data['tahun'] = $anggaran->tahun_anggaran;
			$data['keterangan_anggaran'] = $anggaran->keterangan;
			$data['create_at'] = date("Y-m-d H:i:s");
			$data['create_by'] = user('username');
			insert_data('tbl_budget_plan_neraca',$data);
		endif;
	}
?>