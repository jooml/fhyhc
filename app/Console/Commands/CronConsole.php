<?php
namespace App\Console\Commands;

use App\Http\Service\Manage\JobService;
use App\Models\JobUserDetail;
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
        $res_array = DB::table('yhc_member')->select(['id','bhj_number','members_number','id_card'])->where(["member_type"=>1])->orderBy('id', 'desc')->get();
        $this->info("脚本正在执行");
        if(empty($res_array)) {
            $this->info("未找到目标数据");
            return;
        }


        //以简历id的维度处理数据
        foreach ($res_array as $value) {
            $id_card  =substr($value->id_card,6,4);
            //获取主申请人入户时间
            dd($id_card);die;
            $num = $value->members_number - 1;
            for ($i = 1; $i <= $num; $i++) {
                $temp_id = $value->id + $i;
                //echo $temp_id.PHP_EOL;
                YhcMember::where(['id' => $temp_id])->update(['bhj_number' => $value->bhj_number]);
            }
            $this->info("完成了".$value->bhj_number);
        }
            $this->info("处理完成！");
    }
}
