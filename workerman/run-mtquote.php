<?php
use Workerman\Worker;

use Workerman\Connection\AsyncTcpConnection;
require_once 'Autoloader.php';
$worker = new Worker('http://127.0.0.1:9501');
$worker->onMessage = function($connection, $data){

    $con_url = $data['post']['url'];
//    $tcp_connect = 'tcp://' . "103.28.44.207" . ":" . 443;
    unset($data['post']['url']);
    $tcp_connect = 'tcp://' . $con_url;
    echo '['.date('Y-m-d H:i:s',time()).'] ---' .$tcp_connect . "\n";
    $con = new AsyncTcpConnection($tcp_connect);
    $con->onConnect = function ($con) use($data){
        echo '['.date('Y-m-d H:i:s',time()).'] ---' ."connect success \n";
        $Request_Serial = time() . rand(100, 999);
        $send_data = [];
        conver_type($data['post'],$send_data);
        var_dump($send_data);
        $params = [
            'CstData' => [
                'CstId' => 'CstId',
                'CstPwd' => 'RXXpXOq9l+ZfNg+H4AM8ZLJDr8d8//CquPwe7dvv5e45tatllMJjC6O5Jj7jcwAu4FXsc6ufyhSgwx2OL31o1YVY4yPvpDhekhXS3G/TGR3wy0nOCLuEAaIwjF8GNBui1HVVTNIGoO1sAyqM0i85YYP1ukJLZRr8wjTed6CLJ58='
            ],
            'Request_Serial' => $Request_Serial,
            'Request_Time' => time(),
            'Request_Version' => '1.0.1'
        ];
        $params['Request_Data'] = $send_data;
        // }
        $json = json_encode($params,320);
        // $json = str_replace('\\"','\\\\"',$json);
        $data = "W" . $json . "QUIT\n";
        var_dump($data);
        $con->send($data);
    };
    $con->onMessage = function ($con, $data) use ($connection) {
       echo '['.date('Y-m-d H:i:s',time()).'] ---' ."response success \n";
	 var_dump($data);
      $connection->send($data);
      $connection->close();


    };
    //断开重连
    $con->onClose = function ($con)  use ($connection) {
	        $connection->send(json_encode([]));

       //--------------logs----------------------------------
        $error = "close" . date('Y-m-d H:i:s') . PHP_EOL;
        echo $error;


        //$this->logs($error);
        //--------------logs----------------------------------
        //重新连接
        // $this->index();

    };
    $con->onError = function($con, $code, $msg)
    {
        echo "Error code:$code msg:$msg\n";
    };
    $con->connect();
};
function conver_type ($send_data,&$res){

    if(is_array($send_data)  &&  $send_data){
        foreach($send_data as $k => $v){
            if(is_numeric($v)){
                $res[$k] = (int)$v;
            }else if(is_array($v)){

                conver_type($v,$res[$k]);
            }else{
                $res[$k] = $v;
            }

        }

    }
    return [];
}
Worker::runAll();
