<?php
namespace Admin\Controller;

use Home\Controller\MtquoteController;
use Think\Controller;

class SyncController extends Controller
{
    public function index()
    {


        $this->display();
    }

    public function sync(){
        $symbols = M('symbols')->select();
        if(!$symbols){
            $this->log('品种列表为空，同步结束');
            return false;
        }
        $data = [];
        foreach ($symbols as $k =>$v){
            $data[]=$symbols[$k]['symbol'];
        }
        $response = MtquoteController::Plugin_Get($data);
        if(!$response){
            $this->log('连接mt4失败');
            return false;
        }
        if($response['Rsp_Status'] !==0){
            $this->log('mt4错误失败,错误码：'.$response['Rsp_Info']);
            return false;
        }
        $mt4_symbols = $response['Rsp_Info']['Symbols'];

        foreach ($mt4_symbols as $item){
            $update =  [];
            foreach ($item as $k => $v){
                $update[strtolower($k)] = $v;
            }
            $res = M('symbols')->where(['symbol'=>$item['Symbol']])->save($update);
            if(!$res && $res !== 0){
                $this->log($item['Symbol'].'同步失败');
            }

        }
        $this->log('同步完毕');
    }

    public function echoPro($arr){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }

    public function log($msg){
        echo '['.date('Y-m-d H:i:s',time()).'] '.$msg . PHP_EOL;
    }

    function post($url, $data) {

        //初使化init方法
        $ch = curl_init();

        //指定URL
        curl_setopt($ch, CURLOPT_URL, $url);

        //设定请求后返回结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //声明使用POST方式来进行发送
        curl_setopt($ch, CURLOPT_POST, 1);

        //发送什么数据呢
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


        //忽略证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //忽略header头信息
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //设置超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        //发送请求
        $output = curl_exec($ch);

        //关闭curl
        curl_close($ch);

        //返回数据
        return $output;
    }

}
