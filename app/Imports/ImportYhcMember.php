<?php

namespace App\Imports;

use App\Models\YhcMember;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportYhcMember implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        //判断标题不插入
        if ($row[0] == '备案回执号') {
            return null;
        }

        if ($row[2] == '主申请人') {
            $row[2] = 1;
        }elseif($row[2] == '共同申请人'){
            $row[2] = 2;
        }else{
            $row[2] = 3;
        }

        $data = [
            'bhj_number' => $row[0]??'',
            'name' => $row[1],
            'member_type' => $row[2],
            'id_card' => $row[3],
            'normal_sort' => $row[4] ?? 0,
            'choose_house_sort' => $row[5] ?? 0,
            'members_number' => $row[6] ?? 0,
            'other_info' => $row[7] ?? '',
            'remark' => $row[8] ?? '',
            //'created_at' => date('Y-m-d H:i:s'),
           // 'updated_at' => date('Y-m-d H:i:s'),
        ];
        //dd($data);
        return new YhcMember($data);
    }
}

