<?php

 function dd($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}
function curl_get($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}

function curl_post($url, $data)
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