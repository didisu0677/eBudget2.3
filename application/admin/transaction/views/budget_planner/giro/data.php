<?php
    $bgedit ="";
    $contentedit ="false" ;
    $id = 'keterangan';
    if($access_edit) {
        $bgedit =bgEdit();
        $contentedit ="true" ;
        $id = 'id' ;
    }

    $item   = '';
    $no     = 0;
    $temp_tahun     = '';
    $temp_tahun_d   = '';
    $dataSaved      = [];
    for ($tahun=($anggaran->tahun_anggaran - 1); $tahun <= ($anggaran->tahun_anggaran)  ; $tahun++) { 
        $no++;
        $item .= '<tr>';
        $item .= '<td>'.$no.'</td>';
        if($tahun == $anggaran->tahun_anggaran):
            $item .= '<td>GIRO '.$tahun.' ('.arrSumberData()['renc'].')</td>';
        else:
            $item .= '<td>GIRO '.$tahun.' ('.arrSumberData()['real'].')</td>';
        endif;
        $item .= '<td></td>';
        $item2 = '';
        for ($bln=1; $bln <=12 ; $bln++) { 
            $field  = 'B_' . sprintf("%02d", $bln);
            $field2 = 'P_' . sprintf("%02d", $bln);
            $val = 0;

            if(!isset($arr_not_real[$tahun][$bln])): // untuk realisasi
                if(isset($data_core[$tahun])):
                    if($temp_tahun != $tahun):
                        $temp_tahun = $tahun;
                        $core_key = multidimensional_search($data_core[$tahun], array(
                            'glwnco' => '2100000',
                        ));
                    endif;
                    if(strlen($core_key)>0):
                        $kali_minus = $data_core[$tahun][$core_key]['kali_minus'];
                        $val        = $data_core[$tahun][$core_key][$field];
                        $val        = kali_minus($val,$kali_minus);
                    endif;
                endif;
            else: // untuk selain realisasi
                if($temp_tahun_d != $tahun):
                    $temp_tahun_d = $tahun;
                    $key = multidimensional_search($dt_index_besaran, array(
                        'coa'        => '2100000',
                        'tahun_core' => $tahun,
                    ));
                endif;
                if(strlen($key)>0):
                    $d = $dt_index_besaran[$key];
                    $val = $d['hasil'.$bln];
                endif;
            endif;
            
            $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
            $arrData[$tahun][$bln] = $val;
            $dataSaved['2100000-'.$tahun][$field2] = $val;
          
            $pertumbuhan    = 0;
            $pembagi        = $arrData[($tahun-1)][$bln];
            if($pembagi):
                $pertumbuhan = (($arrData[$tahun][$bln]-$pembagi)/$pembagi)*100;
            endif;
            $item2 .= '<td class="text-right">'.custom_format($pertumbuhan,false,2).'</td>';
           
        }
        $item .= '<td class="border-none bg-white"></td>';
        $item .= '<td class="text-right"></td>';
        $item .= '</tr>';

        // kasda dan non kasda
        if(in_array($tahun,$arr_detail_tahun)):
            $arrDataGiro = [];
            foreach($dt_coa as $k => $v){
                $title = '';
                if($v->coa == '2101011') $title = ' Non Kasda';
                $item .= '<tr>';
                $item .= '<td></td>';
                $item .= '<td>'.$v->coa.' - '.remove_spaces($v->glwdes).$title.'</td>';
                
                $prsnDpk = '';
                $prsnDpktxt = '';
                $keyPrsn = multidimensional_search($arrPrsnDpk,['coa' => $v->coa]);
                if(strlen($keyPrsn)>0){
                    $prsnDpk = round($arrPrsnDpk[$keyPrsn]['prsn'],2);
                    $prsnDpktxt = custom_format($prsnDpk,false,2);
                }
                if($tahun == $anggaran->tahun_anggaran):
                    $item .= '<td style="background: '.$bgedit.'" data-k="'.$k.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;" contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="TOT_'.$cabang->kode_cabang.'" data-id="'.$v->coa.'-rate'.'" data-value="'.$v->rate.'">'.custom_format($v->rate,false,2).'</div></td>';
                else:
                    $item .= '<td class="text-right">'.custom_format($v->rate,false,2).'</td>';
                endif;
                $temp_year = '';
                $temp_year_kasda = '';
                for ($bln=1; $bln <=12 ; $bln++) {
                    $field  = 'B_' . sprintf("%02d", $bln);
                    $field2 = 'P_' . sprintf("%02d", $bln);
                    $bln2   = sprintf("%02d", $bln);
                    $val = 0;
                    if(!isset($arr_not_real[$tahun][$bln])): // untuk realisasi
                        $val = 0;
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
                        $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
                    elseif($v->coa == '2101012'): //untuk giro kasda
                        if($temp_year_kasda != $tahun):
                            $temp_year_kasda = $tahun;
                            $kasda_key = multidimensional_search($dt_giro, array(
                                'coa'        => $v->coa,
                                'tahun_core' => $tahun,
                            ));
                        endif;
                        if(strlen($prsnDpk)>0):
                            $val = $arrData[$tahun][$bln] * $prsnDpk;
                        endif;
                        if(strlen($kasda_key)>0):
                            $changed = json_decode($dt_giro[$kasda_key]['changed']);
                            if(!is_array($changed)): $changed = []; endif;
                            if(in_array($field2,$changed)):
                                $val = $dt_giro[$kasda_key][$field2];
                            endif;
                        endif;
                        $item .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="'.$v->coa.'-tbl_segment-'.$tahun.$bln2.'-'.$v->coa.'-'.$anggaran->id.'-'.$cabang->kode_cabang.'" data-id="'.$tahun.$v->coa.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
                    else: // untuk giro non kasda sebagai buffer
                        $val = $arrData[$tahun][$bln] - $arrDataGiro['2101012-'.$tahun][$bln];
                        $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
                    endif;
                    $arrDataGiro[$v->coa.'-'.$tahun][$bln] = $val;
                    $dataSaved[$v->coa.'-'.$tahun][$field2] = $val;
                }
                $item .= '<td class="border-none bg-white"></td>';
                $item .= '<td class="text-right">'.$prsnDpktxt.'</td>';
                $item .= '</tr>';
            }

            if($tahun == $anggaran->tahun_anggaran):
                foreach (['212' => 'Retail','211' => 'Korporasi'] as $coa => $name) {
                    $prsnDpk = '';
                    $prsnDpktxt = '';
                    $keyPrsn = multidimensional_search($arrPrsnDpk,['coa' => $coa]);
                    if(strlen($keyPrsn)>0){
                        $prsnDpk = round($arrPrsnDpk[$keyPrsn]['prsn'],2);
                        $prsnDpktxt = custom_format($prsnDpk,false,2);
                    }

                    $item .= '<tr>';
                    $item .= '<td></td>';
                    $item .= '<td>--| '.remove_spaces($name).'</td>';
                    $item .= '<td></td>';
                    $temp_year = '';
                    for ($bln=1; $bln <=12 ; $bln++) { 
                        $field2 = 'P_' . sprintf("%02d", $bln);
                        $bln2   = sprintf("%02d", $bln);
                        $val = 0;
                        if($coa == '212'):
                            if($temp_year != $tahun):
                                $temp_year = $tahun;
                                $_key = multidimensional_search($dt_giro, array(
                                    'coa'        => $coa,
                                    'tahun_core' => $tahun,
                                ));
                            endif;
                            if(strlen($prsnDpk)>0):
                                $val = $arrDataGiro['2101011-'.$tahun][$bln] * $prsnDpk;
                            endif;
                            if(strlen($_key)>0):
                                $changed = json_decode($dt_giro[$_key]['changed']);
                                if(!is_array($changed)): $changed = []; endif;
                                if(in_array($field2,$changed)):
                                    $val = $dt_giro[$_key][$field2];
                                endif;
                            endif;
                            $item .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="'.$coa.'-tbl_segment-'.$tahun.$bln2.'-'.$coa.'-'.$anggaran->id.'-'.$cabang->kode_cabang.'" data-id="'.$tahun.$coa.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
                        else:
                            $val = $arrDataGiro['2101011-'.$tahun][$bln] - $arrDataGiro['212-'.$tahun][$bln];
                            $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
                        endif;
                        $arrDataGiro[$coa.'-'.$tahun][$bln] = $val;
                        $dataSaved[$coa.'-'.$tahun][$field2] = $val;
                    }
                    $item .= '<td class="border-none bg-white"></td>';
                    $item .= '<td class="text-right">'.$prsnDpktxt.'</td>';
                    $item .= '</tr>';
                }
            endif;
        endif;
        
        $item .= '<tr>';
        $item .= '<td></td>';
        $item .= '<td>Pert '.$tahun.'</td>';
        $item .= '<td></td>';
        $item .= $item2;
        $item .= '<td class="border-none bg-white"></td>';
        $item .= '<td class="text-right"></td>';
        $item .= '</tr>';
    }

    $where = [
        'anggaran'      => $anggaran,
        'kode_cabang'   => $cabang->kode_cabang,
    ];
    data_saved($dataSaved,$where);

    echo $item;

    function data_saved($dt,$p1){
        $anggaran       = $p1['anggaran'];
        $kode_cabang    = $p1['kode_cabang'];

        foreach($dt as $k => $v){
            $x      = explode('-',$k);
            $coa    = $x[0];
            $tahun  = $x[1];
            $where  = [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'tahun_core'    => $tahun,
                'coa'           => $coa,
            ];

            $ck = get_data('tbl_budget_plan_giro',[
                'select' => 'id',
                'where'  => $where
            ])->row();
            
            $data = $v;
            $data['id']     = '';
            if($tahun == $anggaran->tahun_anggaran):
                $data['parent_id'] = '0';
            else:
                $data['parent_id'] = null;
            endif;
            if($ck):
                $data['id'] = $ck->id;
            else:
                $data = array_merge($data,$where);
                $data['tahun_anggaran'] = $anggaran->id;
            endif;
            save_data('tbl_budget_plan_giro',$data,[],true);
        }
    }
?>