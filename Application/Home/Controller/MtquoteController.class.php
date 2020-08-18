<?php


namespace Home\Controller;


class MtquoteController
{
    public static function request_mt4_post($param = [],$server ='')
    {
        $url = '127.0.0.1:9501';

        if(!empty($server)){
            $server_url = $server;
        } else {
            $config = M('plug_config')->find();
            $server_url = $config['plug_ip'] . ':' . $config['plug_port'];
        }
        $param['url'] = $server_url;
      if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;

        $p = http_build_query($param);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return json_decode(trim($data),true);
    }

    public static function plugin_flag($flag = 1,$server = '',$no_repeat_set = 0){
        $data = [
            'Cmd'=>'Plugin_Flag',
            'Client'=>'client',
            'Clientpwd'=>'clientpwd',
            'Clientip'=>'clientpip',
            'ClientGroup'=>'groupnumber',
            'Flag'=>$flag,
            'NoRepeatSet'=>$no_repeat_set
        ];
        return self::request_mt4_post($data,$server);
    }
        //
////            'Symbols'=>[
////                [
////                    'Symbol'=>'EURJPY',
////                    'Flag'=>0
////                ],
////                [
////                    'Symbol'=>'EURUSD',
////                    'Flag'=>1,
////                    'Mode'=>4,
////                    'Value'=>100,
////                ],
////            ],
//            "Symbols"=>
//              [
//                  "EURJPY",
//                  "EURUSD"
//              ]
    public static function plugin_set($param = []){
        $data = [
            'Cmd'=>'Plugin_Set',
            'Client'=>'client',
            'Clientpwd'=>'clientpwd',
            'Clientip'=>'clientpip',
            'ClientGroup'=>'groupnumber',
            'Symbols'=>$param,
        ];
        return self::request_mt4_post($data);
    }
    public static function Plugin_Get($param = []){
        $data = [
            'Cmd'=>'Plugin_Get',
            'Client'=>'client',
            'Clientpwd'=>'clientpwd',
            'Clientip'=>'clientpip',
            'ClientGroup'=>'groupnumber',
            'Symbols'=>$param,
        ];
        return self::request_mt4_post($data);
    }

}