<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        $post = I("post.");
        if($post){
            $username = isset($post['username']) ? $post['username'] : false;
            $password = isset($post['password']) ? $post['password'] : false ;
            if(!$username  ||  !$password)$this->error("缺少参数");
            $user = M("user")->where(['name'=>$username,'password'=>md5($password)])->find();
//            var_dump($user);exit;
            if(!$user)$this->error("用户名或密码错");
            if($user['is_admin'] != "2")$this->error("用户未找到");
            session("admin_id",$user['id']);
            $post['password'] = md5($post['password']);
            $data = [
                'type' => 1,
                'login_address'=>2,
                'ip'=>$_SERVER['REMOTE_ADDR'],
                'time'=>date('Y-m-d H:i:s',time()),
                'content'=>json_encode($post),
            ];
            M('logs')->add($data);
            $this->redirect("Admin/Index/index");
            exit;
        }

        $this->display();
    }



}