<?php

namespace Home\Controller;

use Think\Controller;
use Think\Db;
use Think\Model;

class IndexController extends Controller
{

    public function index()
    {

        $this->checkLogin();
        $page = I("page");
        $size = 25;
     /*   if (!session('uid')) {
            $this->redirect('login/index', '未登录');
        }*/
      
        $user = M("user")->where(['id' => session("uid")])->find();
        $where = [];
		$symbols = [];
            $symbols = explode(',', $user['symbols']);
            $where = ['id' => ['in', $symbols]];
        $list = $this->get_page("symbols", $where, $page, $size);
     
      $this->assign(['username'=>$user['name']]);
        $this->assign(['list' => $list['list'], 'show' => $list['show']]);
        $this->display('index');

    }
  
  public function checkLogin()
    {
    
        if (!session("uid")) {
            $this->redirect("/login/index");
        }
    }


    public function login()
    {
        $post = I('post.');
        if ($post) {
            if (!isset($post['username']) || empty($post['username'])) {
                $this->error('请输入您的用户名', 'login');
            }
            $User = M('User');
            $res = $User->where(['name' => $post['username']])->find();
            if (empty($res)) {
                $this->error('未找到用户', 'login');
            } else {

                if ($res['password'] != md5($post['password'])) {
                    $this->error('用户名或密码错误', 'login');
                }
                unset($res['password']);
                session("uid", $res['id']);
                $this->success("登录成功", "index");
                exit;
            }
            exit;
        }
        $this->display('login');
    }

    public function symbol_list()
    {
        $this->checkLogin();
        $page = I("page");
        $size = 25;
        if (!session('uid')) {
            $this->redirect('login', '未登录');
        }
        $user = M("user")->where(['id' => session("uid")])->find();
        $symbols = explode(',', $user['symbols']);
        $where = ['id' => ['in', $symbols]];
        $this->assign(['username'=>$user['name']]);
        $this->display();
    }
    public function symbol_page(){
        $input = I("");
        $page = $input['page'] > 1 ? $input['page'] - 1 : 0;
        $offset = $page * $input['limit'];
        $user = M("user")->where(['id' => session("uid")])->find();
		 $symbols = explode(',', $user['symbols']);
        $where = ['id' => ['in', $symbols]];
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

    public function test()
    {
        var_dump(MtquoteController::plugin_flag(1));
    }

    public function welcome(){
        echo "首页";
    }

    public function logout(){
        session("uid",null);
        $this->redirect("index/login");
    }

    public function post_set_symbol()
    {
        $post = I('post.');
        $id = I("id");
    	$ss = M('symbols')->where(['id' => $id])->find();
    	
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
                'NoRepeatSet'=> (int)$post['no_repeat_set']
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
                'no_repeat_set'=>$post['no_repeat_set']
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


    }

    public function set_symbol()
    {

        $id = I("id");
       $symbol = M('symbols');
        $ss = $symbol->where(['id' => $id])->find();
        $this->assign('symbol',$ss);
        $this->assign("id", $id);
        $this->display('Index/set_symbol');
    }


    public
    function get_page($table, $where, $page, $size)
    {
        $channel = M($table);
        $list = $channel->where($where)->order('id desc')->page($page . ',' . $size)->select();
        $this->assign('list', $list);// 赋值数据集
        $count = $channel->where($where)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count, $size);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        return ['list' => $list, 'show' => $show];
    }
}