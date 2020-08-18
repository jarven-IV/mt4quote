<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
    public $user = [];
    public function __construct()
    {
        parent::__construct();

        $this->checkLogin();
        $this->getUserInfo();
        $this->assign("username",$this->user['username']);
    }

    public function checkLogin(){
        if(!session("admin_id")){
            $this->redirect("admin/login/index");
        }
    }
    public function getUserInfo(){
        $uid = session("admin_id");

        $this->user = M("user")->where(['id'=>$uid])->find();
        if(!$this->user){
            $this->redirect("/login/index");
        }
        return $this->user;
    }
    public function get_page($table,$where,$page,$size){
        $channel = M($table);
        $list =  $channel->where($where)->order('id desc')->page($page.','.$size)->select();
        $this->assign('list',$list);// 赋值数据集
        $count      = $channel->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$size);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->lastSuffix=false;
		$Page->setConfig('header','<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;&nbsp;每页<b>%LIST_ROW%</b>条&nbsp;&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','末页');
		$Page->setConfig('first','首页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);
        return ['list'=>$list,'show'=>$show];
    }


}