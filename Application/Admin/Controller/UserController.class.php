<?php
namespace Home\Controller;

use Think\Controller;

class UserController extends CommonController
{
    public function index()
    {


        $this->display();
    }

    public function user_list()
    {


        $table = "user";
        $where = [];
        $page = I("p");
        $size = 25;

        if ($this->user['identity'] == "admin") {
            //管理员
            $page_data = $this->get_page($table, $where, $page, $size);
        } else {
            //通道管理员
            //代理商
            $page_data = $this->get_page($table, ['id' => $this->user['id']], $page, $size);

        }

        $data = $page_data['list'];

        $this->assign('page', $page_data['show']);
        $this->assign('list', $data);
        $this->assign("identity", $this->user['identity']);
        $this->display();
    }

    public function user_add()
    {
        if ($this->user['identity'] != "admin") {

            die("非法操作");

        }

        $channel = M("channel")->select();


        $this->assign('channel', $channel);
        $this->display();
    }

    public function user_create()
    {
        if (IS_POST) {

            $user = M("user");

            $has_user = $user->where(['username' => I("post.username")])->count();
            if ($has_user) {
                $this->ajaxReturn(['status' => 0, 'msg' => '该昵称已经存在']);
            }
            $has_channel = $user->where(['channel'=>I("post.channel")])->count();
            if ($has_channel) {
                $this->ajaxReturn(['status' => 0, 'msg' => '该通道已经存在管理员']);
            }
            $post = I("post.");
            if(!isset($post['channel']) || !isset($post['username']) || !isset($post['password'])){
                $this->ajaxReturn(['status'=>0,'msg'=>'参数错误']);
            }
            if($post['password'] != $post['re_password']){
                $this->ajaxReturn(['status'=>0,'msg'=>'两次密码不一致']);
            }
            $post['key'] = md5(rand(1,99999));
            $post['identity'] = "channel_admin";
            $post['create_time'] = time();
            $post['created_at'] = date("Y-m-d H:i:s",time());
            $post['created_by'] = $this->user['id'];
            $post['status'] = 2;
            $post['password'] = md5($post['password']);
            $res = $user->add($post);
            if (!$res) $this->ajaxReturn(['status' => 0, 'msg' => '添加失败']);
            $this->ajaxReturn(['status' => 1, 'msg' => '添加成功']);
        }
    }

    public function change_user_status(){
        $id = I("id");
        $status = M("user")->where(['id'=>$id])->getField("status");
        if($status == 1){
            $res = M("user")->where(['id'=>$id])->setField("status",2);
        }else{
            $res = M("user")->where(['id'=>$id])->setField("status",1);
        }

        if (!$res) $this->ajaxReturn(['status' => 0, 'msg' => M()->getLastSql()]);
        $this->ajaxReturn(['status' => 1, 'msg' => '修改成功']);
    }


}