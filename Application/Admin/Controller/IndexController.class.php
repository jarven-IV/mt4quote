<?php

namespace Admin\Controller;

use Home\Controller\MtquoteController;
use Think\Cache\Driver\Db;
use Think\Controller;

class IndexController extends CommonController
{


    public function test()
    {
        echo 123;
    }

    /**
     *下单地址：http://game.bitradex.info
     *
     */


    public function index()
    {
        $user = $this->getUserInfo();
        $this->assign('username',$user['name']);
        $this->display('index');


    }

    public function symbol_list()
    {
        $page = I("page");
        $size = 25;
        // $list = $this->get_page("symbols", [], $page, $size);
        // $this->assign(['list' => $list['list'], 'page' => $list['show']]);
        $this->display();
    }
    
    public function symbol_page(){
        $input = I("");
        $page = $input['page'] > 1 ? $input['page'] - 1 : 0;
        $offset = $page * $input['limit'];
        $build = M("symbols")->where($where)->limit($offset,$input['limit'])->order('id desc')->select();

        // $list = $this->get_page("symbols", [], $page, $size);
        // $this->assign(['list' => $list['list'], 'page' => $list['show']]);
         $count = M("symbols")->where($where)->count();
        if($build)$this->ajaxReturn([
            'code'=>0,
            'msg'=>"获取成功",
            'count'=>$count,
            'data'=>$build
        ]);
    }


    public function addSymbol()
    {

        $post = I("post.");
        if ($post) {
            if (!isset($post['symbol']) || empty($post['symbol'])) {
                $this->ajaxReturn(['status' => 0, 'msg' => '请输入品种名']);
            }
            if(empty(M('plug_config')->find())){
                $this->ajaxReturn(['status'=>0,'msg'=>'未配置插件信息，请先配置！']);
            }
       		$post['symbol'] = stripslashes(htmlspecialchars_decode($post['symbol']));
            $symbol_model = M('symbols');
            $symbol = $symbol_model->where(['symbol' => $post['symbol']])->find();
            if (!empty($symbol)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '该品种已存在，请勿重复添加']);
            }
            $response = MtquoteController::Plugin_Get([$post['symbol']]);
            if($response['Rsp_Status']!== 0){
                $this->ajaxReturn(['status'=>0,'msg'=>'添加失败'.$response['Rsp_Info']]);
            }
            $response = $response['Rsp_Info']['Symbols'][0];
            $data = ['symbol' => $post['symbol'], 'flag'=>$response['Flag'], 'mode' =>$response['Mode'], 'value'=>$response['Value']];//////////////////这里调用一次接口，获取品种信息
            if (empty($data)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '品种错误']);
            }
            $res = $symbol_model->add($data);
            if ($res) {
                $this->ajaxReturn(['status' => 1, 'msg' => '添加品种成功']);
            }
        }
        $this->display();
    }

    public function userAdd()
    {

        $sym = M('symbols')->select();

        $this->assign('symbols',$sym);
        $post = I("post.");
        if ($post) {
            if (!isset($post['username']) || empty($post['username'])) {
                $this->ajaxReturn(['status' => 0, 'msg' => '用户名不能为空']);
            }
            if (!isset($post['password']) || empty($post['password'])) {
                $this->ajaxReturn(['status' => 0, 'msg' => ['密码不能为空']]);
            }
            if (isset($post['re_password']) && $post['password'] != $post['re_password']) {
                $this->ajaxReturn(['status' => 0, 'msg' => '两次密码不一致']);
            }
            $user = M('User');
            $res = $user->where(['name' => $post['username']])->find();
            if ($res) {
                $this->ajaxReturn(['status' => 0, 'msg' => '用户名已存在']);
            }
            unset($post['re_password']);
            $post['name'] = $post['username'];
            $post['password'] = md5($post['password']);
            $post['created_at']=date('Y-m-d H:i:s',time());
            $post['is_admin']  = 1;
            $res = $user->add($post);
            if ($res) {
                $this->ajaxReturn(['status' => 1, 'msg' => '添加成功']);
            }
            $this->ajaxReturn(['status' => 0, 'msg' => '添加失败']);
        }
        $this->display('Index/user_add');
    }

    public function user_list()
    {
        $page = I("page");
        $size = 25;
        $list = $this->get_page("user", [], $page, $size);
        $this->assign(['list' => $list['list'], 'show' => $list['show']]);
        $this->display();
    }

    public function set_symbol()
    {

        $post = I('post.');
        $id = I("id");
    	$ss = M('symbols')->where(['id' => $id])->find();
        $this->assign('symbol',$ss);
        $this->assign("id", $id);
        $ss['symbol'] = stripslashes(htmlspecialchars_decode($ss['symbol']));
        if ($post) {
//            $flag = MtquoteController::Plugin_Get(['EURUSD']);
//            if($flag['Rsp_Info']['Flag'] != 1){
//                $this->ajaxReturn(['status'=>0,'msg'=>'插件未启用']);
//            }
            $symbol = M('symbols');
            $ss = $symbol->where(['id' => $post['id']])->find();
             if(isset($post['mode']) ){
             	if($post['value'] ==''){
             		$this->ajaxReturn(['status'=>0,'msg'=>'value值不能为空']);
             	}
                if(empty($post['value']) && $post['value'] !=0){
                    if($post['mode'] == 2){
                        $this->ajaxReturn(['status'=>0,'msg'=>'请输入行情值,值为百分比']);
                    } elseif ($post['mode'] == 4 || $post['mode'] == 5){
                        $this->ajaxReturn(['status'=>0,'msg'=>'请输入行情值,请输入基点']);
                    }
                }
                if($post['mode'] >= 6){
                    $this->ajaxReturn(['status'=>0,'msg'=>'模式错误，请设置1-5']);
                }
            }
            $param = [[
                'Symbol' => $ss['symbol'],
                'Flag' => $post['flag'] ? 1 : 0 ,
                'Mode' => (empty($post['mode']) && $post['mode'] != 0) ? $ss['mode'] :$post['mode'],
                'Value' => (empty($post['value']) && $post['value'] != 0)?  $ss['value'] : $post['value'],
                // 'NoRepeatSet'=> (int)$post['no_repeat_set']
            ]];
            $mtset = MtquoteController::plugin_set($param);
            if($mtset['Rsp_Status'] !== 0){
                    $this->ajaxReturn(['status' => 0, 'msg' => '设置失败,错误码:'.$mtset['Rsp_Info']]);
                }
            // $response = MtquoteController::Plugin_Get([$ss['symbol']]);
            // if ($response['Rsp_Status'] !== 0) {
            	
            //     $this->ajaxReturn(['status' => 0, 'msg' => '设置失败,错误码:'.$response['Rsp_Info']]);
            // }
            // if($response['Rsp_Info']['Flag'] == 0){
            //     $this->ajaxReturn(['status'=>0,'msg'=>'设置失败，插件未启用']);
            // }
            // $symbols = $response['Rsp_Info']['Symbols'][0];
            $data = [
                'symbol' => $ss['symbol'],
                'flag' => $post['flag'],
                'mode' => $post['mode'],
                'value' => $post['value'],
                // 'no_repeat_set'=>$post['no_repeat_set']
            ];
            $res = M('symbols')->where(['id' => $post['id']])->save($data);
            if ($res || $res === 0) {
                $logs = [
                    'type'=>2,//修改品种值
                    'login_address'=>2,
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                    'time'=>date('Y-m-d H:i:s'),
                    'content'=>json_encode($post),
                ];
                M('logs')->add($logs);
                $this->ajaxReturn(['status' => 1, 'msg'=>'设置成功']);
            }
            $this->ajaxReturn(['status' => 0, 'msg'=>'设置失败']);

        }
        $this->display('Index/set_symbol');
    }

    public function setUserSymbol(){
        $user = M('user')->where(['id'=>I('id')])->find();
        $sym = M('symbols')->select();
        $symbols = explode(',',$user['symbols']);
        foreach ($sym as $k=>$v){
            if($user->is_admin > 1){
                continue;
            }
            $sym[$k]['checked'] = 0;
            if(in_array($v['id'],$symbols)){
                $sym[$k]['checked'] = 1;
            }
        }
        $this->assign('userinfo',$user);
        $this->assign('symbols',$sym);
        $post = I("post.");

        if($post){
            $res = M('user')->where(['id'=>$post['id']])->save(['symbols'=>$post['symbols']]);
            if ($res) {
                $this->ajaxReturn(['status' => 1, 'msg'=>'设置成功']);
            }
            $this->ajaxReturn(['status' => 0, 'msg'=>'设置失败']);
        }
        $this->display('Index/set_user_symbol');
    }

    public function plug_status(){
        $flag = MtquoteController::Plugin_Get(['EURUSD']);
        $this->assign('flag',$flag['Rsp_Info']['Flag']);
        $post = I('post.');
        if($post){
            $flag = MtquoteController::plugin_flag($post['flag']);
            if($flag['Rsp_Info']['Flag'] == $post['flag']){
                $res = M('plug_config')->save(['status'=>$post['flag']]);
                $this->ajaxReturn(['status'=>1,'msg'=>'设置成功']);
            }
            $this->ajaxReturn(['status'=>0,'msg'=>'设置失败，'.$flag['Rsp_Info']]);
        }
        $this->display();

    }
    public function welcome(){
        echo "首页";
    }
    public function logout(){
        session("uid",null);
        $this->redirect("Login/index");
    }

    public function delUser(){
        $id = I('id');
        if($id){
            if(!isset($id) || empty($id)){
                $this->ajaxReturn(['status'=>0,'msg'=>'缺失参数']);
            }
            $user = M('user')->where(['id'=>$id])->find();
            if(empty($user)){
                $this->ajaxReturn(['status'=>0,'msg'=>'未找到用户']);
            }
            $del = M('user')->where(['id'=>$id])->delete();
            if($del){
                $this->ajaxReturn(['status'=>1,'msg'=>'删除成功']);
            }
            $this->ajaxReturn(['status'=>0,'删除失败']);
        }
    }
    public function delSymbol(){
        $id = I('id');
        if($id){
            if(!isset($id) || empty($id)){
                $this->ajaxReturn(['status'=>0,'msg'=>'缺失参数']);
            }
            $symbol = M('symbols')->where(['id'=>$id])->find();
            if(empty($symbol)){
                $this->ajaxReturn(['status'=>0,'msg'=>'未找到品种']);
            }
            $users = M('user')->select();
            foreach ($users as $k =>$v){
                if(stripos($v['symbols'],$id)){
                    $tmp = explode(',',$v['symbols']);
                    $index = array_search($id,$tmp);
                    unset($tmp[$index]);
                    $users[$k]['symbols'] = implode(',',$tmp);
                }
            }
            $del = M('symbols')->where(['id'=>$id])->delete();
            if($del){
                $this->ajaxReturn(['status'=>1,'msg'=>'删除成功']);
            }
            $this->ajaxReturn(['status'=>0,'删除失败']);
        }
    }

    public function plugConfig(){
        $config = M('plug_config')->find();
        $this->assign('config',$config);
        $this->display('plug_config');
    }
    public function plugSet(){
        $post = I('post.');
        $config = M('plug_config')->find();
        $this->assign('config',$config);
        if($post){
            if(empty($post['plug_ip']) || empty($post['plug_port'])){
                $this->ajaxReturn(['status'=>0,'msg'=>'请填写插件信息！']);
            }
          if(!filter_var($post['plug_ip'], FILTER_VALIDATE_IP)){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入正确的ip地址']);
            }
          if($post['plug_port'] > 65535){
                            $this->ajaxReturn(['status'=>0,'msg'=>'请输入正确的端口号']);
          }
            $data = ['plug_ip'=>$post['plug_ip'],'plug_port'=>$post['plug_port'],'no_repeat_set'=>$post['no_repeat_set']];
            $response = MtquoteController::plugin_flag(1,$post['plug_ip'] . ':' . $post['plug_port'],$post['no_repeat_set']);
            if(empty($response)){
                $this->ajaxReturn(['status'=>0,'msg'=>'连接失败，请检查']);
            }
            if ($response['Rsp_Status'] !== 0) {
                $this->ajaxReturn(['status' => 0, 'msg' => '设置失败,错误码：'.$response['Rsp_Info']]);
            }

            $data['status'] = $response['Rsp_Info']['Flag'];
            if($config){
            	$symbols = M('symbols')->select();
                if(!empty($symbols)){
                    // $param = [];
                    $init = [];
                    foreach ($symbols as $k=>$v){
                        // $param[] = [
                        //     'Symbol'=>$symbols[$k]['symbol'],
                        //     'Value'=>0,
	                       // 'Mode'=>$symbols[$k]['mode'],
                        //     'Flag'=>$symbols[$k]['flag'],
                        // ];
                        $init[] = [
                            'Symbol'=>$symbols[$k]['symbol'],
                            'Mode'=>$symbols[$k]['mode'],
                            'Value'=>$symbols[$k]['value'],
                            'Flag'=>$symbols[$k]['flag']
                        ];
                    }
                 //   $allset = MtquoteController::plugin_set($param);
	                // if($allset['Rsp_Status'] !==0){
	                //     $this->ajaxReturn(['status'=>0,'msg'=>'清除当前配置失败，错误码：'.$allset['Rsp_Info']]);
	                // }
	                $res = M('plug_config')->where(['id'=>1])->save($data);
	                $newplug_init = MtquoteController::plugin_set($init);

                  if($newplug_init['Rsp_Status'] !==0){
	                    $this->ajaxReturn(['status'=>0,'msg'=>'初始化插件失败,错误码：'.$newplug_init['Rsp_Info']]);
	                }
	                if($newplug_init['Rsp_Info']['Flag'] != 1){
	                    $this->ajaxReturn(['status'=>0,'msg'=>'新的插件未启用']);
	                }
                } else {
                	$res = M('plug_config')->where(['id'=>1])->save($data);
                }
               
        	} else {
                $res = M('plug_config')->add($data);
            }
            if($res || $res === 0){
                $this->ajaxReturn(['status'=>1,'msg'=>'设置成功']);
            }
            $this->ajaxReturn(['status'=>0,'msg'=>'设置失败']);
            exit;
        }
        $this->display('plug_set');
    }

}
