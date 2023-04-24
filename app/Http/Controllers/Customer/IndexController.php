<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Imports\ImportYhcMember;
use App\Models\YhcChooseResult;
use App\Models\YhcMember;
use App\Services\ImportService;
use Curl\Curl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Rtgm\sm\RtSm2;
use Rtgm\util\FormatSign;

class IndexController extends Controller
{
    /**
     * 导入excel数据
     * @param Request $request
     * @return JsonResponse
     */
    public function index (Request $request)
    {
        $file = $request->file('file');
        Excel::import(new ImportYhcMember,$file);

        echo 'success';
    }


    public function change(Request $request){
         set_time_limit(0);
         $result = YhcMember::select(['id','bhj_number','members_number','id_card'])->where(['member_type'=>1])->get()->toArray();
         dd($result);die;
         foreach ($result as $key=>$value) {
             $num = $value['members_number'] - 1;
             for ($i=1; $i<=$num;$i++) {
                $temp_id = $value['id'] + $i;
                //echo $temp_id.PHP_EOL;
                 YhcMember::where(['id'=>$temp_id])->update(['bhj_number'=>$value['bhj_number']]);
             }

         }

         echo 'success';

    }

    public function getChooseHouse(Request $request){

        set_time_limit(0);
        $requestData  = $request->all();
        $totalPage = $requestData['page'] ?? 76;

        $headerArray  = array("Cookie: DASCSESSIONID=5c7487c7-3f76-41d3-b935-78c4c489bd41;");

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
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
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
        var_dump($a);

        curl_close($ch);
        return $result;
    }



    public function getAllZjj(Request $request){
        $url = "https://zjj.sz.gov.cn/zfxx/bzflh/lhmc/getLhmcList";
        $option = [
            'pageSize'=>100,
            'waittype'=>'04ec3e88478a01c2d751bbbe1038edbcf2eeaf544fe603dac5b7fcb6314b214a90aad75eeee063ef1f8b0aea9366640b55bbe7c8146c3a20ece3003c699c59cb169bf22f7aa9a29c8e5f369cc6d878d123fa423f607a62d21c527b523cb61e1acfa2ca688c',
        ];
        $header = array("Content-type: application/json");

        $totalPage = 4229;
        for($i=1;$i <= $totalPage;$i++ ){
            $option['page'] = $i;
            $option['pageNumber'] = $i;

            $result = self::curlPost($url, $option, 5, $header, 'json');
            dd($result);
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

        }
    }
}
