<?php
namespace Home\Controller;
use Admin\Controller\CommonController;
use Think\Controller;
use Think\Db;
use Think\Model;

class LoginController extends Controller {

    public function index(){

        $post = I('post.');
        if($post){
            if(!isset($post['username']) || empty($post['username'])){
                $this->error('请输入您的用户名','/Index/login');
            }
            $User = M('User');
            $res =  $User->where(['name'=>$post['username']])->find();
            if(empty($res)){
                $this->error('未找到用户','/Index/login');
            } else {

                if($res['password'] != md5($post['password'])){
                    $this->error('用户名或密码错误','/Index/login');
                }
                unset($res['password']);
                session("uid",$res['id']);
                $post['password'] = md5($post['password']);
                $data = [
                    'type' => 1,
                    'login_address'=>1,
                    'ip'=>$_SERVER['REMOTE_ADDR'],
                    'time'=>date('Y-m-d H:i:s',time()),
                    'content'=>json_encode($post),
                ];
                M('logs')->add($data);
                $this->success("登录成功","/Index/index");


            }
            
        }
        $this->display('/Index/login');
    }


}