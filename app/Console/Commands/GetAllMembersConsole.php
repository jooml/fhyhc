<?php
namespace App\Console\Commands;

use App\Models\YhcAllMember;
use Illuminate\Console\Command;


class GetAllMembersConsole extends Command
{

    protected $signature = 'getall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取轮候库所有数据';

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
        $url = "https://zjj.sz.gov.cn/zfxx/bzflh/lhmc/getLhmcList";
        $option = [
            'pageSize'=>100,
            'waittype'=>'04ec3e88478a01c2d751bbbe1038edbcf2eeaf544fe603dac5b7fcb6314b214a90aad75eeee063ef1f8b0aea9366640b55bbe7c8146c3a20ece3003c699c59cb169bf22f7aa9a29c8e5f369cc6d878d123fa423f607a62d21c527b523cb61e1acfa2ca688c',
        ];
        $header = array("Content-type: application/json");

        $totalPage = 119;
        for($i=119;$i <= $totalPage;$i++ ){
            sleep(2);
            $option['page'] = $i;
            $option['pageNumber'] = $i;

            $result = self::curlPost($url, $option, 5, $header, 'json');
            $result  = json_decode($result,true);
            $inser = [];

            if(isset($result['data']['list']) && !empty($result['data']['list'])) {
                foreach ($result['data']['list'] as $key=>$value) {
                    $inser[$key]['bhj_number'] = $value['shoulhzh'];
                    $inser[$key]['name'] = $value['xingm'];
                    $inser[$key]['id_card'] = $value['sfzh'];
                    $inser[$key]['ruh_time'] = $value['ruhsj'] ?? '';
                    $inser[$key]['shouccbsj'] = $value['shouccbsj'] ?? '';
                    $inser[$key]['normal_sort'] = $value['paix'];
                }

                YhcAllMember::insert($inser);
                $this->info("总共{$totalPage}页，当前执行第{$i} 页");
            }

        }

        $this->info("处理完成！");
    }

    /**
     * 传入数组进行HTTP POST请求
     */
    public function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "") {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if($data_type == 'json'){
            $post_string = json_encode($post_data);

        }elseif($data_type == 'array') {
            $post_string = $post_data;
        }elseif(is_array($post_data)){
            $post_string = http_build_query($post_data, '', '&');
        }

        $ch = curl_init();    // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36'); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);

        // 打印请求的header信息
        $a = curl_getinfo($ch);

        curl_close($ch);
        return $result;
    }
}
