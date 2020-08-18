<?php
require_once 'Autoloader.php';
use Workerman\Worker;
use Workerman\Lib\Timer;
$worker = new Worker();
$synclist = [
    // 'http://laravel.bitradex.info',
    'http://manager.1vpn.net/admin/sync/sync',
    'http://mt4plug.1vpn.net/admin/sync/sync',
    'http://mt4.1vpn.net/admin/sync/sync',
    'http://mt.1vpn.net/admin/sync/sync',
];
$worker->onWorkerStart = function () use ($synclist){
    $index = 0;
    Timer::add(5,function () use ($index,$synclist){
        $file_index = file_get_contents('sync_logs');
        if($file_index){
            $index = $file_index;
        }
        echo '['. date('Y-m-d H:i:s',time()) . '] ' .$synclist[$index] . ' 开始同步' . PHP_EOL;
        $res = curl_get($synclist[$index] . '/admin/sync/sync');
        echo $res . PHP_EOL;
        echo '['. date('Y-m-d H:i:s',time()) . '] ' .$synclist[$index] . ' 同步结束' . PHP_EOL;
        $index++;
        if($index>3){
            $index = 0;
        }
        file_put_contents('sync_logs',$index);
    });
};

function curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $dom = curl_exec($ch);
    curl_close($ch);
    return $dom;
}
Worker::runAll();
