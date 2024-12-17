<?php
  $bgedit ="";
  $contentedit ="false" ;
  $id = 'keterangan';
  if($access_edit) {
      $bgedit =bgEdit();
      $contentedit ="true" ;
      $id = 'id' ;
  }

	$item  = '';
	$no    = 0;
  $arrSaved = [];
  foreach($groups as $group){
    $item .= '<tr>';
    $item .= '<td></td>';
    $item .= '<td>'.remove_spaces($group->glwdes).'</td>';
    $item .= '</tr>';

    $item_first = '';
    $item_last  = '';
    $item_buffer= '';
    $item_buffer_close  = '';
    $after_buffer       = false;
    $coa_buffer         = '';
    $arrTotal           = [];
    foreach($dt_coa[$group->coa] as $k => $v){
      $no++;
      $rate = $v->rate;

      if(in_array($v->coa,$buffer)):
        $row = 'item_buffer';
        $after_buffer = true;
        $coa_buffer   = $v->coa;
      elseif(!$after_buffer):
        $row = 'item_first';
      else:
        $row = 'item_last';
      endif;

      $last_year = $anggaran->tahun_terakhir_realisasi;
      $c_key = multidimensional_search($data_core[$last_year], array(
          'glwnco' => $v->coa,
      ));

      $real   = 0;
      $real2  = 0;
      $netto  = 0;
      if(strlen($c_key)>0):
        $field      = 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi - 1));
        $kali_minus = $data_core[$last_year][$c_key]['kali_minus'];
        $val        = $data_core[$last_year][$c_key][$field];
        $real2      = kali_minus($val,$kali_minus);

        $field      = 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
        $val        = $data_core[$last_year][$c_key][$field];
        $real       = kali_minus($val,$kali_minus);
      endif;

      $netto_key = multidimensional_search($dt_kredit, array(
        'coa'         => $v->coa,
        'tahun_core'  => $anggaran->tahun_anggaran
      ));
      if(strlen($netto_key)>0):
        $netto = $dt_kredit[$netto_key]['netto'];
      endif;

      ${$row} .= '<tr>';
      ${$row} .= '<td>'.$no.'</td>';
      ${$row} .= '<td>|--- '.$v->coa.' - '.remove_spaces($v->glwdes).'</td>';
      if($access_edit):
        ${$row} .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;" contenteditable="'.$contentedit.'" contenteditable="true" class="edit-value text-right" data-name="TOT_'.$cabang->kode_cabang.'" data-id="rate-'.$v->coa.'" data-value="'.$rate.'">'.custom_format($rate,false,2).'</div></td>';
      else:
        ${$row} .= '<td class="text-right">'.custom_format($rate,false,2).'</td>';
      endif;
      if(!in_array($v->coa,$buffer)):
        $temp_tahun = $last_year;
        $temp_year  = '';
        $bln_before = $real;
        foreach($detail_tahun as $k2 => $v2){
          $bln   = $v2->bulan;
          $field = 'B_' . sprintf("%02d", $bln);
          $field2= 'P_' . sprintf("%02d", $bln);
          $bulan = sprintf("%02d", $bln);
          $tahun = $v2->tahun;
          $val   = $bln_before;
          if(!isset($arr_not_real[$tahun][$bln])):
            if($temp_tahun != $tahun):
              $temp_tahun = $tahun;
              $c_key = multidimensional_search($data_core[$tahun], array(
                'glwnco' => $v->coa,
              ));
            endif;
            if(strlen($c_key)>0):
              $kali_minus = $data_core[$tahun][$c_key]['kali_minus'];
              $val        = $data_core[$tahun][$c_key][$field];
              $val        = kali_minus($val,$kali_minus);
            endif;
            ${$row} .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
          else:
            if($temp_year != $tahun):
              $temp_year = $tahun;
              $key = multidimensional_search($dt_kredit, array(
                'coa'         => $v->coa,
                'tahun_core'  => $tahun
              ));
            endif;
            if($netto):
              $val += $netto;
            else:
              $val = $real;
            endif;
            if(strlen($key)>0):
              $is_edit = $dt_kredit[$key]['is_edit']; if($is_edit) $is_edit = json_decode($is_edit,true); else $is_edit = [];
              if(isset($is_edit[$field2])):
                $val = $is_edit[$field2];
              endif;
            endif;

            $val = round_value($val);

            $bln_before = $val;
            ${$row} .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;" contenteditable="'.$contentedit.'" contenteditable="true" class="edit-value text-right" data-name="'.$field2.'" data-id="kredit-'.$v->coa.'-'.$tahun.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
          endif;
          if(isset($arrTotal[$tahun][$bln])) $arrTotal[$tahun][$bln] += $val; else $arrTotal[$tahun][$bln] = $val;
          $dataSaved[$v->coa.'-'.$tahun][$field2] = $val;
        }
      endif;

      if(in_array($v->coa,$buffer)):
        $row = 'item_buffer_close';
      endif;

      ${$row} .= '<td class="border-none"></td>';
      if(!in_array($v->coa,$buffer)):
        ${$row} .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;" contenteditable="'.$contentedit.'" contenteditable="true" class="edit-value text-right" data-name="netto" data-id="netto-'.$v->coa.'" data-value="'.view_report($netto).'">'.custom_format(view_report($netto)).'</div></td>';
      else:
        ${$row} .= '<td class="text-right">'.custom_format(view_report($netto)).'</td>';
      endif;
      
      ${$row} .= '<td class="border-none"></td>';
      ${$row} .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
      ${$row} .= '<td class="text-right">'.custom_format(view_report($real2)).'</td>';
      ${$row} .= '</tr>';
    }

    $item .= $item_first;
    $item .= $item_buffer;

    $temp_year = '';
    foreach($detail_tahun as $k2 => $v2){
      $field2 = 'P_' . sprintf("%02d", $v2->bulan);
      $kredit = 0;
      if($temp_year != $v2->tahun):
        $temp_year = $v2->tahun;
        $key = multidimensional_search($dt_kredit, array(
          'coa'         => $group->coa,
          'tahun_core'  => $v2->tahun
        ));
      endif;
      if(strlen($key)>0):
        $kredit = $dt_kredit[$key][$field2];
      endif;

      $val = $kredit - $arrTotal[$v2->tahun][$v2->bulan];

      $val = round_value($val);

      $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
      $dataSaved[$coa_buffer.'-'.$v2->tahun][$field2] = $val;
      if(isset($arrTotal[$v2->tahun][$v2->bulan])) $arrTotal[$v2->tahun][$v2->bulan] += $val; else $arrTotal[$v2->tahun][$v2->bulan] = $val;
      

    }
    $item .= $item_buffer_close;
    $item .= $item_last;

    $item .= '<tr>';
    $item .= '<td></td>';
    $item .= '<td><b> Total '.remove_spaces($group->glwdes).'</b></td>';
    $item .= '<td></td>';
    foreach($detail_tahun as $k2 => $v2){
      $item .= '<td class="text-right"><b>'.custom_format(view_report($arrTotal[$v2->tahun][$v2->bulan])).'</b></td>';
    }
    $item .= '<td class="border-none bg-white"></td>';
    $item .= '<td class="border-none bg-white"></td>';
    $item .= '<td class="border-none bg-white"></td>';
    $item .= '<td class="border-none bg-white"></td>';
    $item .= '<td class="border-none bg-white"></td>';
    $item .= '</tr>';
  }

  $where = [
      'anggaran'      => $anggaran,
      'kode_cabang'   => $cabang->kode_cabang,
  ];
  data_saved($dataSaved,$where);

	echo $item;
?>