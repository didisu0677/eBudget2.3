<?php
$no = 0;
$item = '';

// aktiva & pasiva
$dt_neraca = [];
foreach($coa_aktiva_pasiva as $k => $v){
	$noTxt = '';
	if($k == 0):
		$no++;
		$noTxt = $no;
	endif;
	$item .= '<tr>';
	$item .= '<td width="30">'.$noTxt.'</td>';
	$item .= '<td>'.$v->glwnco." - ".remove_spaces($v->glwdes).'</td>';
	$key = multidimensional_search($neraca,['coa' => $v->glwnco]);
	foreach($detail_tahun as $v2){
		$field = 'B_' . sprintf("%02d", $v2->bulan);
		if(strlen($key)>0):
			$val = $neraca[$key][$field];
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$val = 0;
			$item .= '<td class="text-center">#</td>';
		endif;
		if($v->glwnco == '1000000'):
			$dt_neraca[$field] = checkNumber($val);
		else:
			$dt_neraca[$field] -= checkNumber($val);
		endif;
	}
	$item .= '</tr>';
}
$item .= '<tr>';
$item .= '<td></td>';
$item .= '<td>'.lang('selisih_neraca').'</td>';
foreach($detail_tahun as $v2){
	$field = 'B_' . sprintf("%02d", $v2->bulan);
	if(isset($dt_neraca[$field]) && $dt_neraca[$field]):
		$val = $dt_neraca[$field];
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	else:
		$item .= '<td class="text-center">-</td>';
	endif;
}
$item .= '</tr>';
$item .= kolom_kosong($detail_tahun);


// laba
$nama_laba = 'laba';
if($coa_laba):
	$nama_laba = $coa_laba->glwnco." - ".remove_spaces($coa_laba->glwdes);
endif;
$no++;
$item .= '<tr>';
$item .= '<td>'.$no.'</td>';
$item .= '<td>'.$nama_laba.'</td>';
foreach($detail_tahun as $v2){
	if($laba):
		$val = $laba->{'bulan_'.$v2->bulan};
		$val = round_value($val);
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	else:
		$item .= '<td class="text-center">#</td>';
	endif;
}
$item .= '</tr>';

$item .= '<tr>';
$item .= '<td></td>';
$item .= '<td>'.lang('laba_rugi_sd_bulan').'</td>';
foreach($detail_tahun as $v2){
	if($laba_sd_bulan):
		$val = $laba_sd_bulan->{'bulan_'.$v2->bulan};
		$val = round_value($val);
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	else:
		$item .= '<td class="text-center">#</td>';
	endif;
}
$item .= '</tr>';

$item .= '<tr>';
$item .= '<td></td>';
$item .= '<td>'.lang('selisih_labarugi').'</td>';
foreach($detail_tahun as $v2){
	$val  = 0;
	$val2 = 0;
	if($laba) $val = $laba->{'bulan_'.$v2->bulan};
	if($laba_sd_bulan) $val2 = $laba_sd_bulan->{'bulan_'.$v2->bulan};
	$val  = round_value($val);
	$val2 = round_value($val2);

	$val = checkNumber($val) - checkNumber($val2);
	if($val):
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	else:
		$item .= '<td class="text-center">-</td>';
	endif;
}
$item .= '</tr>';
$item .= kolom_kosong($detail_tahun);

// kolektibilitas produktif
$no++;
$item .= '<tr>';
$item .= '<td>'.$no.'</td>';
$item .= '<td>'.lang('kol3_produktif').'</td>';
foreach($detail_tahun as $v2){
	$item .= '<td></td>';
}
$item .= '</tr>';
foreach($kol3_produktif as $k => $v){
	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.($k+1).'. '.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'B_' . sprintf("%02d", $v2->bulan);
		$val = $v->{$field};
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

// kolektibilitas konsumtif
$no++;
$item .= '<tr>';
$item .= '<td>'.$no.'</td>';
$item .= '<td>'.lang('kol3_konsumtif').'</td>';
foreach($detail_tahun as $v2){
	$item .= '<td></td>';
}
$item .= '</tr>';
foreach($kol3_konsumtif as $k => $v){
	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.($k+1).'. '.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'B_' . sprintf("%02d", $v2->bulan);
		$val = $v->{$field};
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

// kolektibilitas KUP
$no++;
$nama_kup = 'KUP';
if($coa_kup):
	$nama_kup = $coa_kup->glwnco.' - '.remove_spaces($coa_kup->glwdes);
endif;
$item .= '<tr>';
$item .= '<td>'.$no.'</td>';
$item .= '<td>'.$nama_kup.'</td>';
foreach($detail_tahun as $v2){
	$item .= '<td></td>';
}
$item .= '</tr>';
for ($i=2; $i <= 5 ; $i++) { 
	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.lang('kol').' '.$i.'</td>';
	foreach($detail_tahun as $v2){
		$field = 'B_' . sprintf("%02d", $v2->bulan);
		if($kol_kup && strlen($kol_kup->{$field.'_'.$i})>0):
			$val = $kol_kup->{$field.'_'.$i};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$item .= '<td class="text-center">#</td>';
		endif;
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

// kolektibilitas PLO
$no++;
$nama_plo = 'PLO';
if($coa_kup):
	$nama_plo = $coa_plo->glwnco.' - '.remove_spaces($coa_plo->glwdes);
endif;
$item .= '<tr>';
$item .= '<td>'.$no.'</td>';
$item .= '<td>'.$nama_plo.'</td>';
foreach($detail_tahun as $v2){
	$item .= '<td></td>';
}
$item .= '</tr>';
for ($i=2; $i <= 5 ; $i++) { 
	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.lang('kol').' '.$i.'</td>';
	foreach($detail_tahun as $v2){
		$field = 'B_' . sprintf("%02d", $v2->bulan);
		if($kol_plo && strlen($kol_plo->{$field.'_'.$i})>0):
			$val = $kol_plo->{$field.'_'.$i};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$item .= '<td class="text-center">#</td>';
		endif;
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

// kredit
$no++;
$item .= '<tr>';
$item .= '<td>'.$no.'</td>';
$item .= '<td>'.lang('kredit').'</td>';
foreach($detail_tahun as $v2){
	$item .= '<td></td>';
}
$item .= '</tr>';
foreach($kredit as $k => $v){
	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'P_' . sprintf("%02d", $v2->bulan);
		if(strlen($v->{$field})>0):
			$val = $v->{$field};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$item .= '<td class="text-center">#</td>';
		endif;
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

foreach($ecl as $k => $v){
	$noTxt = '';
	if($k == 0):
		$no++;
		$noTxt = $no;
	endif;
	$item .= '<tr>';
	$item .= '<td>'.$noTxt.'</td>';
	$item .= '<td>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'B_' . sprintf("%02d", $v2->bulan);
		if(strlen($v->{$field})>0):
			$val = $v->{$field};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$item .= '<td class="text-center">#</td>';
		endif;
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

foreach($ckpn as $k => $v){
	$no++;
	$noTxt = $no;
	$item .= '<tr>';
	$item .= '<td>'.$noTxt.'</td>';
	$item .= '<td>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'bulan_'.$v2->bulan;
		if(strlen($v->{$field})>0):
			$val = $v->{$field};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$item .= '<td class="text-center">#</td>';
		endif;
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

$coa_4152128 = [];
foreach($pend_plo as $k => $v){
	if($v->glwnco == '4152128'):
		$coa_4152128 = $v;
	endif;
	$noTxt = '';
	if($k == 0):
		$no++;
		$noTxt = $no;
	endif;
	$item .= '<tr>';
	$item .= '<td>'.$noTxt.'</td>';
	$item .= '<td>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'bulan_'.$v2->bulan;
		if(strlen($v->{$field})>0):
			$val = $v->{$field};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$item .= '<td class="text-center">#</td>';
		endif;
	}
	$item .= '</tr>';
}
$item .= kolom_kosong($detail_tahun);

$effect_rate = [];
if($coa_4152128):
	$v = $coa_4152128;
	$no++;
	$item .= '<tr>';
	$item .= '<td>'.$no.'</td>';
	$item .= '<td>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'bulan_'.$v2->bulan;
		$field2 = 'B_' . sprintf("%02d", $v2->bulan);
		if(strlen($v->{$field})>0):
			$val = $v->{$field};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$val = 0;
			$item .= '<td class="text-center">#</td>';
		endif;
		$effect_rate['awal'][$field2] = $val;
	}
	$item .= '</tr>';
endif;
foreach($neraca_plo as $k => $v){
	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</td>';
	foreach($detail_tahun as $v2){
		$field = 'B_' . sprintf("%02d", $v2->bulan);
		if(strlen($v->{$field})>0):
			$val = $v->{$field};
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		else:
			$val = 0;
			$item .= '<td class="text-center">#</td>';
		endif;
		$effect_rate['pembagi'][$field] = $val;
	}
	$item .= '</tr>';
}

$item .= '<tr>';
$item .= '<td></td>';
$item .= '<td>Effective Rate</td>';
foreach($detail_tahun as $v2){
	$field = 'B_' . sprintf("%02d", $v2->bulan);
	$pembagi = 1;
	if(isset($effect_rate['pembagi'][$field]) && $effect_rate['pembagi'][$field]):
		$pembagi = checkNumber($effect_rate['pembagi'][$field]);
	endif;
	$val = 0;
	if(isset($effect_rate['awal'][$field])):
		$val = (checkNumber($effect_rate['awal'][$field])/$v2->bulan) * 12;
	endif;
	$val = ($val/$pembagi)*100;
	$item .= '<td class="text-right">'.custom_format($val,false,2).'</td>';
}
$item .= '</tr>';
$item .= kolom_kosong($detail_tahun);

// biaya
$no++;
$val_biaya = 0;
$key = multidimensional_search($biaya,['kode' => 'labarugi']);
if(strlen($key)>0):
	$val_biaya = $biaya[$key]['value'];
endif;
$item .= '<tr>';
$item .= '<td width="30">'.$no.'</td>';
$item .= '<td>'.lang('biaya').' = '.custom_format($val_biaya).'</td>';
for ($i=1; $i <= count($detail_tahun) ; $i++) { 
	$item .= '<td class="border-none bg-white"></td>';
}
$item .= '</tr>';
$item .= kolom_kosong($detail_tahun);

echo $item;

function kolom_kosong($detail_tahun){
	$item = '<tr>';
	$item .= '<td class="border-none bg-white"></td>';
	$item .= '<td class="border-none bg-white white">.</td>';
	for ($i=0; $i < count($detail_tahun) ; $i++) { 
		$item .= '<td class="border-none bg-white"></td>';
	}
	$item .= '</tr>';
	return $item;
}
?>