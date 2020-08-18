<?php
namespace Home\Controller;

use Think\Controller;

class ChannelController extends CommonController
{

    public function channel_list()
    {

        $table = "channel";
        $where = [];
        $page = I("p");
        $size = 25;

        if ($this->user['identity'] == "admin") {
            //管理员
            $page_data = $this->get_page($table, $where, $page, $size);
        } else {
            //通道管理员
            //代理商
            $page_data = $this->get_page($table, ['name' => $this->user['channel'], 'uid' => $this->user['id']], $page, $size);

        }

        $data = $page_data['list'];
        if ($data) {
            $order = M("order");
            foreach ($data as $k => &$v) {
                $channel_name = $v['name'];
                $v['amount'] = $order->where(['channel' => $channel_name])->sum("amount");
                $v['apply_amount'] = $order->where(['channel' => $channel_name, 'status' => 2])->sum("apply_amount");
            }
        }
        $this->assign('page', $page_data['show']);
        $this->assign('list', $data);

        $this->display();
    }

    public function channel_add()
    {


        $this->display();
    }


    public function channel_create()
    {
        if (IS_POST) {

            $channel = M("channel");
            $has_channel = $channel->where(['name' => I("post.name")])->count();
            if ($has_channel) {
                $this->ajaxReturn(['status' => 0, 'msg' => '通道代号唯一，不可重复']);
            }
            $post = I("post.");
            $post['created_at'] = date("Y-m-d H:i:s");$post['create_time'] = time();
            $channel->create($post);
            $res = $channel->add();
            if (!$res) $this->ajaxReturn(['status' => 0, 'msg' => '添加失败']);
            $this->ajaxReturn(['status' => 1, 'msg' => '添加成功']);
        }
    }

    public function channel_del()
    {
        $id = I("id");
        $del_res = M("channel")->where(['id' => $id])->delete();
        if (!$del_res) $this->ajaxReturn(['status' => 0, 'msg' => '删除失败']);
        $this->ajaxReturn(['status' => 1, 'msg' => '删除成功']);
    }


}