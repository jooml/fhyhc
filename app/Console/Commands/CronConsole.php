<?php
namespace App\Console\Commands;

use App\Http\Service\Manage\JobService;
use App\Models\JobUserDetail;
use App\Models\YhcAllMember;
use App\Models\YhcMember;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class CronConsole extends Command
{
    protected $signature = 'change:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("脚本正在执行");

        DB::table('yhc_member')->select(['id','bhj_number','members_number','id_card'])->where(["member_type"=>1])->orderBy('id', 'asc')->chunk(500, function ($res_array) {
            //以简历id的维度处理数据
            foreach ($res_array as $value) {
                $res = YhcAllMember::where(['bhj_number'=>$value->bhj_number])->first();
                YhcMember::where(['id' => $value->id])->update(['ruh_time'=>$res->ruh_time,'shouccbsj'=>$res->shouccbsj]);
                //获取主申请人入户时间
                $num = $value->members_number - 1;
                for ($i = 1; $i <= $num; $i++) {
                    $temp_id = $value->id + $i;
                    $temp_value = YhcMember::where(['id' => $temp_id])->first();
                    $id_card = substr($temp_value->id_card, 6, 4);
                    $temp_data['both_year'] = $id_card;
                    $temp_data['bhj_number'] = $value->bhj_number;
                    $temp_data['ruh_time'] = $res->ruh_time;
                    $temp_data['shouccbsj'] = $res->shouccbsj;

                    YhcMember::where(['id' => $temp_id])->update($temp_data);
                }
//                $temp_other = YhcMember::where(['id' => $temp_id+1])->first();
//                if($temp_other->member_type == 3 && empty($temp_other->bhj_number)) {
//                    YhcMember::where(['id' => $temp_id+1])->update(['bhj_number'=>$value->bhj_number]);
//                }
                $this->info("完成了" . $value->bhj_number);
            }
            $this->info("处理完成！");
        });
    }

}
