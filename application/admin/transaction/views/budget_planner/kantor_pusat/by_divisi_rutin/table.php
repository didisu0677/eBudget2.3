<?php
	$item = 0;
	$ii  = 0;
	if(count($list)>0):
		foreach ($header as $h) {
			$rowspan = 0;
			$no = 0;
			$ii += 1;
			foreach ($list as $k => $v) {
				
				if($v->kegiatan == $h):
					$no += 1;
					$bgedit ="";
					$contentedit ="false" ;
					$id = 'keterangan';
					if($akses_ubah) {
						$bgedit =bgEdit();
						$contentedit ="true" ;
						$id = 'id' ;
					}
					
					$kegiatan = explode('__',$v->kegiatan);
					$default_status = true;
					if(count($kegiatan)>1):
						$default_status = false;
						$kegiatan = $kegiatan[1];
					else:
						$kegiatan = $kegiatan[0];
					endif;

					$item .= '<tr>';
					if($no == 1):
						$name = strtolower($h);
            			$name = preg_replace("/[^a-z0-9]+/", "", $name);
            			$rowSpan = 1;
            			if(isset(${'count_'.$name})):
            				$rowSpan = ${'count_'.$name};
            			endif;
						$item .= '<td rowspan="'.$rowSpan.'">'.$ii.'</td>';
						$item .= '<td rowspan="'.$rowSpan.'">'.$kegiatan.'</td>';
					endif;
					$item .= '<td>'.$v->glwnco.'</td>';
					$item .= '<td>'.remove_spaces($v->glwdes).'</td>';
					$item .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="pd_bulan" data-id="'.$v->id.'" data-value="'.view_report($v->pd_bulan).'">'.custom_format(view_report($v->pd_bulan)).'</div></td>';
					for ($i = 1; $i <= 12; $i++) { 
						$v_field  = 'T_' . sprintf("%02d", $i);
						$value = $v->{$v_field};

						$item .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="'.$v_field.'" data-id="'.$v->id.'" data-value="'.view_report($value).'">'.custom_format(view_report($value)).'</div></td>';
					}
					if($akses_ubah && $default_status):
						$item .= '<td class="button"><button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="'.$v->id.'" title="'.lang('ubah').'"><i class="fa-edit"></i></button></td>';
					else:
						$item .= '<td></td>';
					endif;
					
					$item .= '</tr>';
				endif;
			}
		}
	else:
		$item .= '<tr><th colspan="'.(12+5).'">Data Not Found</th></tr>';
	endif;
	echo $item;
?>