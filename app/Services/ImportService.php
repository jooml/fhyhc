<?php


namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;

class ImportService implements ToArray {

    public $type;

    public function __construct (string $type = '') {
        $this->type = $type;
    }

    public function array (array $array): void {
        switch ($this->type) {
            case 'gm':
            case 'create':
            case 'active':
            case 'pay':
                break;
            default:
                $this->handleData($array);
                // dd( Carbon::parse('1970-01-01')->addDays(44707-25569)->toDateTimeString());
                // 结果 2022-05-26 00:00:00
                break;
        }
    }

    public function handleData (array $array) {
        // 去除表头
        unset($array[0]);
        $dateTime = Carbon::now()->toDateTimeString();
        collect($array)->chunk(1000)->map(function ($value) use ($dateTime) {
            $insertData = collect($value)->map(function ($val) use ($dateTime) {
                return [
                    'date'       => Carbon::parse('1970-01-01')->addDays($val[0]-25569)->toDateTimeString(),
                    'name'       => $val[1],
                    'status'     => $val[2],
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ];
            })->toArray();
            // 插入数据
        });
    }

}
