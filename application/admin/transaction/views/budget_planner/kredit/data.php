<?php
    $item = '';
    $no = 0;
    $dataSaved = [];
    foreach ($arr_tahun_seconds as $tahun) {
        
        // produktif dan konsumtif
        $item2      = '';
        $arrTotal   = [];
        foreach($dt_coa as $k => $v){
            $item2 .= '<tr>';
            $item2 .= '<td></td>';
            $item2 .= '<td>'.$v->coa.' - '.remove_spaces($v->glwdes).'</td>';
            $temp_year  = '';
            $temp_year2 = '';
            for ($bln=1; $bln <= 12 ; $bln++) { 
                $field  = 'B_' . sprintf("%02d", $bln);
                $field2 = 'P_' . sprintf("%02d", $bln);
                $bln2   = sprintf("%02d", $bln);
                $val = 0;
                if(!isset($arr_not_real[$tahun][$bln])): // untuk realisasi
                    if(isset($data_core[$tahun])):
                        if($temp_year != $tahun):
                            $temp_year = $tahun;
                            $c_key = multidimensional_search($data_core[$tahun], array(
                                'glwnco' => $v->coa,
                            ));
                        endif;
                        if(strlen($c_key)>0):
                            $kali_minus = $data_core[$tahun][$c_key]['kali_minus'];
                            $val        = $data_core[$tahun][$c_key][$field];
                            $val        = kali_minus($val,$kali_minus);
                        endif;
                    endif;
                else:
                    if($temp_year2 != $tahun):
                        $temp_year2 = $tahun;
                        $key = multidimensional_search($dt_index_besaran, array(
                            'coa'        => $v->coa,
                            'tahun_core' => $tahun,
                        ));
                    endif;
                    if(strlen($key)>0):
                        $d = $dt_index_besaran[$key];
                        $val = $d['hasil'.$bln];
                    endif;
                endif;
                $item2 .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
                if(isset($arrTotal[$bln])) $arrTotal[$bln] += $val; else $arrTotal[$bln] = $val;
                $dataSaved[$v->coa.'-'.$tahun][$field2] = $val;
            }
            $item2 .= '</tr>';
        }

        // total kredit
        $no++;
        $title = '('.arrSumberData()['real'].')';
        if($tahun == $anggaran->tahun_anggaran) $title = '('.arrSumberData()['renc'].')';
        $item .= '<tr>';
        $item .= '<td>'.$no.'</td>';
        $item .= '<td>TOTAL KREDIT '.$tahun.' '.$title.'</td>';
        $temp_tahun = '';
        $item_pert  = ''; // item pertumbuhan
        for ($bln=1; $bln <=12 ; $bln++) { 
            $field  = 'B_' . sprintf("%02d", $bln);
            $val = 0;
            if(isset($arrTotal[$bln])) $val = $arrTotal[$bln];
            $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
            $data_core_sum[$tahun][$field] = $val;

            $pertumbuhan    = 0;
            $pembagi        = $data_core_sum[($tahun-1)][$field];
            if($pembagi):
                $pertumbuhan = (($data_core_sum[$tahun][$field]-$pembagi)/$pembagi)*100;
            endif;
            $item_pert .= '<td class="text-right">'.custom_format($pertumbuhan,false,2).'</td>';
        }
        $item .= '</tr>';
        $item .= $item2;

        // pertumbuhan
        $item .= '<tr>';
        $item .= '<td></td>';
        $item .= '<td>Pert '.$tahun.'</td>';
        $item .= $item_pert;
        $item .= '</tr>';
    }

    $where = [
        'anggaran'      => $anggaran,
        'kode_cabang'   => $cabang->kode_cabang,
    ];
    data_saved($dataSaved,$where);

    echo $item;
?>