<?php
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

            $ck = get_data('tbl_budget_plan_kredit',[
                'select' => 'id',
                'where'  => $where
            ])->row();
            
            $data = $v;
            $data['id']     = '';
            if($tahun == $anggaran->tahun_anggaran):
                $data['parent_id'] = '0';
            else:
                $data['parent_id'] = $kode_cabang;
            endif;
            if($ck):
                $data['id'] = $ck->id;
            else:
                $data = array_merge($data,$where);
                $data['tahun_anggaran'] = $anggaran->tahun_anggaran;
                $data['keterangan_anggaran'] = $anggaran->keterangan;
            endif;
            save_data('tbl_budget_plan_kredit',$data,[],true);
        }
    }
?>