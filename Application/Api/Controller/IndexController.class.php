<?php
namespace Api\Controller;

use Think\Controller;

class IndexController extends Controller
{





    public function testApi()
    {


        $url = "http://api.huiyuandao.com/token?shop_id=8004398&app_key=2111976&secret=651f5f5c78343c1d4b7a40dcce7acace";
      //  $access_token = "5906fda88dfb07077eab76cf1ff03deafc073f326011372a3b75f429105cd8a4";

        $access_token = json_decode($this->get($url), true)['access_token'];


        $order_no = "201909261846125247";
        $path = "http://api.huiyuandao.com/order/info?access_token=" . $access_token . "&order_no=" . $order_no;

        $order = json_decode(curl_get($path), true);
        $data['order_no'] = $order['order_no'];// echo $path;
        $data['status'] = $order['status'];
        $data['payment'] = $order['payment'];
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    function get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    function post($url, $data)
    {

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


    function get_sign($client_secret, $arr)
    {
        ksort($arr); //将数组按键名排序，按键名升序排列
        $tmp = '';
        foreach ($arr as $k => $v) {
            if (is_array($v)) { //如果当前值为数组时则该数组内的各项也要拼接到字符串中
                ksort($v); //按键名排序
                foreach ($v as $m => $n) {
                    $tmp .= $m . $n; //拼接字符串
                }
            } else {
                $tmp .= $k . $v; //拼接字符串
            }
        }
        return strtoupper(md5($client_secret . $tmp . $client_secret)); //先用md5加密，再转成大写
    }


    public function index()
    {

        echo 1;
    }
}