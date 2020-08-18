<?php
namespace Home\Controller;

use Think\Controller;

class UrlController extends CommonController
{
    public function index()
    {


        $this->display();
    }



    public function get_code_by_channel()
    {
        $channel_name = I("name");
        $table = "pay_url";
        $page = I("p");
        $size = 25;


        if ($this->user['identity'] == "admin") {
            //管理员
            $page_data = $this->get_page($table, ['channel'=>$channel_name], $page, $size);
        } else {
            //通道管理员
            //代理商
            $page_data = $this->get_page($table, ['channel'=>$channel_name], $page, $size);
        }
        $data = $page_data['list'];
        if ($data) {
            $order = M("order");
            foreach ($data as $k => &$v) {
                $channel_name = $v['channel'];
                $v['amount'] = $order->where(['channel' => $channel_name])->sum("amount");
                $v['apply_amount'] = $order->where(['channel' => $channel_name, 'status' => 2])->sum("amount");
            }
        }


        $this->assign('page', $page_data['show']);
        $this->assign('list', $data);

        $this->display();

    }

    public function url_list(){

        $table = "pay_url";
        $page = I("p");
        $size = 25;


        if ($this->user['identity'] == "admin") {
            //管理员
            $page_data = $this->get_page($table, [], $page, $size);
        } else {
            //通道管理员
            //代理商
            $page_data = $this->get_page($table, ['channel'=>$this->user['channel']], $page, $size);
        }
        $data = $page_data['list'];
        if ($data) {
            $order = M("order");
            foreach ($data as $k => &$v) {
                $url = $v['url'];
                $v['amount'] = $order->where(['url' => $url])->sum("amount");
                $v['apply_amount'] = $order->where(['url' => $url, 'status' => 2])->sum("apply_amount");
            }
        }


        $this->assign('page', $page_data['show']);
        $this->assign('list', $data);

        $this->display();
    }

    public function url_add(){
        if ($this->user['identity'] == "admin") {

            $channel = M("channel")->select();

        }else{

            $user_channel = $this->user['channel'];
            $channel = M("channel")->where(['name'=>$user_channel])->select();
        }


        $this->assign('channel',$channel);
        $this->display();
    }

    public function url_create(){
        if (IS_POST) {

            $url = M("pay_url");

            $has_channel = $url->where(['url' => I("post.url")])->count();
            if ($has_channel) {
                $this->ajaxReturn(['status' => 0, 'msg' => '该付款码已经存在']);
            }

            $post = I("post.");
            $post['create_time'] = time();
            $post['created_at'] = date("Y-m-d H:i:s");
            $post['uid'] = session("uid");

            $res = $url->add($post);
            if (!$res) $this->ajaxReturn(['status' => 0, 'msg' => '添加失败']);
            $this->ajaxReturn(['status' => 1, 'msg' => '添加成功']);
        }
    }

    public function url_change_status(){
        $id = I("id");
        $pay_url = M("pay_url")->where(['id'=>$id])->find();
        $channel = M("channel")->select();
        $this->assign("id",$id);
        $this->assign("status",$pay_url['status']);
        $this->assign("channel",$channel);
        $this->assign("pay_url",$pay_url);
        $this->display();
    }
    public function url_change_status_by_id(){
        $id = I("id");
        $channel = I("channel");
        if(!$id || !$channel){
            $this->ajaxReturn(['status'=>0,'msg'=>'参数错误']);
        }
        $res = M("pay_url")->where(['id'=>$id])->setField("channel",$channel);
        if (!$res) $this->ajaxReturn(['status' => 0, 'msg' => '通道未改变']);
        $this->ajaxReturn(['status' => 1, 'msg' => '转移成功']);
    }



    public function url_del(){
        $id = I("id");
        if(!$id)$this->ajaxReturn(['status'=>0,'msg'=>'参数错误']);
        $res = M("pay_url")->where(['id'=>$id])->delete();
        if (!$res) $this->ajaxReturn(['status' => 0, 'msg' => '删除失败']);
        $this->ajaxReturn(['status' => 1, 'msg' => '删除成功']);
    }


}
