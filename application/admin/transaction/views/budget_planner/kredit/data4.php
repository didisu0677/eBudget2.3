<?php
    $bgedit ="";
    $contentedit ="false" ;
    $id = 'keterangan';
    if($access_edit) {
        $bgedit =bgEdit();
        $contentedit ="true" ;
        $id = 'id' ;
    }

    $item = '';
    $no = 0;
    $dataSaved = [];
    foreach($dt_coa as $coa){
        $no++;

        $total      = $coa->total;
        $tambahan   = 10;
        $item .= '<tr>';
        $item .= '<td>'.$no.'</td>';
        $item .= '<td>'.remove_spaces($coa->glwdes).'</td>';

        $bln_before = $total;
        $temp_tahun = '';
        foreach($detail_tahun as $k => $v){
            $bln    = $v->bulan;
            $tahun  = $v->tahun;
            $field2 = 'P_' . sprintf("%02d", $bln);

            $val = $bln_before;
            if($temp_tahun != $tahun):
                $temp_tahun = $tahun;
                $key = multidimensional_search($kredit, array(
                    'coa'           => $coa->coa,
                    'tahun_core'    => $tahun,
                ));
            endif;
            if(strlen($key)>0):
                $tambahan = $kredit[$key]['index_kali'];
                $is_edit  = $kredit[$key]['is_edit'];
                if($is_edit) $is_edit = json_decode($is_edit,true); else $is_edit = [];
                
                $val += $tambahan;
                if(isset($is_edit[$field2])):
                    $val = $is_edit[$field2];
                endif;
            else:
                $val += $tambahan;
            endif;
            $bln_before = $val;

            $item .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;" contenteditable="'.$contentedit.'" contenteditable="true" class="edit-value text-right" data-name="'.$field2.'" data-id="rekening-'.$coa->coa.'-'.$tahun.'" data-value="'.$val.'">'.custom_format($val).'</div></td>';
            $dataSaved[$coa->coa.'-'.$tahun][$field2] = $val;
        }
        
        $item .= '<td class="border-none"></td>';
        $item .= '<td class="text-right">'.custom_format($total).'</td>';
        $item .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;" contenteditable="'.$contentedit.'" contenteditable="true" class="edit-value text-right" data-name="index_kali" data-id="index_kali-'.$coa->coa.'" data-value="'.$tambahan.'">'.custom_format($tambahan).'</div></td>';
        $item .= '</tr>';
    }
    echo $item;

    $where = [
      'anggaran'      => $anggaran,
      'kode_cabang'   => $cabang->kode_cabang,
    ];

    data_saved($dataSaved,$where);

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

            $ck = get_data('tbl_jumlah_rekening',[
                'select' => 'id',
                'where'  => $where
            ])->row();
            
            $data = $v;
            $data['id']     = '';
            
            if($ck):
                $data['id'] = $ck->id;
            else:
                $data = array_merge($data,$where);
                $data['tahun_anggaran'] = $anggaran->tahun_anggaran;
                $data['keterangan_anggaran'] = $anggaran->keterangan;
                $data['index_kali'] = 10;
            endif;
            save_data('tbl_jumlah_rekening',$data,[],false);
        }
    }
?>