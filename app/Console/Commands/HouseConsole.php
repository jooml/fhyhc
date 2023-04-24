<?php
namespace App\Console\Commands;

use App\Http\Service\Manage\JobService;
use App\Models\JobUserDetail;
use App\Models\YhcAllMember;
use App\Models\YhcChooseResult;
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

class HouseConsole extends Command
{
    protected $signature = 'house';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取house数据';

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

        $headerArray  = array("Cookie: DASCSESSIONID=5c7487c7-3f76-41d3-b935-78c4c489bd41;");
        $totalPage = 76;
        for($i=1;$i <= $totalPage;$i++ ){
            $url = 'https://zjj.sz.gov.cn/zfxx/ggfw/public/selectHouse/publicityList?xfpwh=&bahzh=&zt=%E5%B7%B2%E9%80%89%E6%88%BF&pageIndex=%d&pageSize=100&pcdm=SJPZ20213481&sqrxm=&xdxm=&xfrq=&episodeGuid=';
            $url  = urldecode($url);
            $url = sprintf($url,$i);
            $result = self::geturl($url,$headerArray);
            $inser = [];
            if(isset($result['data']['content']) && !empty($result['data']['content'])) {
                foreach ($result['data']['content'] as $key=>$value) {
                    $inser[$key]['bhj_number'] = $value['BAHZH'];
                    $inser[$key]['name'] = $value['SQRXM'];
                    $inser[$key]['house_type'] = 1;
                    $inser[$key]['choose_result'] = $value['DMC'].$value['XDFH'];
                }
                YhcChooseResult::insert($inser);
            }

            $this->info("完成了第" . $i.'页');

        }
        echo 'success';
    }


    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */

    public static function geturl($url,$headerArray=array("Content-type:application/json;", "Accept:application/json"))
    {
        $header = $headerArray;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        return $output;
    }

}
