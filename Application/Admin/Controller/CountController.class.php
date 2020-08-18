<?php
namespace Home\Controller;
use Think\Controller;
class CountController extends CommonController {
    public function index(){

//        var_dump($this->user);
        $this->assign("username",$this->user['username']);
        $this->display();
    }


    public function logout(){
        session("uid",null);
        $this->user = [];
        $this->redirect("/login/index");
    }


    public function welcome(){
        $this->assign("username",$this->user['username']);
        $this->display();
    }


}