<?php
namespace Api\Controller;

use Think\Controller;

class PayController extends Controller
{

    public function get_page($table,$where,$page,$size){
        $channel = M($table);
        $list =  $channel->where($where)->order('id desc')->page($page.','.$size)->select();
        $this->assign('list',$list);// 赋值数据集
        $count      = $channel->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$size);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        return ['list'=>$list,'show'=>$show];
    }



     public function pay_add(){
         $this->display();
     }

    public function pay_url(){

        $arr = $this->get_page("pay_url",[],0,10);
        $this->assign($arr);
        $this->display();
    }

    /**
     * 添加付款链接
     */
    public function create_pay_url(){
        $order_no = I("order_no");
        $pay_url = I("url");
        $amount = I('amount');

        if(!is_numeric($amount)){
            $this->ajaxReturn(['status'=>0,'msg'=>'金额必须是数字']);
        }
        $pay_model = M("pay_url");
        $order = $this->get_order($order_no);

        if(!$order){
            $this->ajaxReturn(['status'=>0,'msg'=>'未找到该笔订单']);
        }
        if($order['status'] != 0){
            $this->ajaxReturn(['status'=>0,'msg'=>'该笔订单已经付款']);

        }
        if($order['amount'] != $amount){
            $this->ajaxReturn(['status'=>0,'msg'=>'代付订单金额与填写金额不一致']);
        }

        if(!$pay_url){
            $this->ajaxReturn(['status'=>0,'msg'=>'付款链接不能为空']);
        }
        $has_url = $pay_model->where(['order_no'=>$order_no])->find();
        if($has_url){
            $this->ajaxReturn(['status'=>0,'msg'=>'该笔订单已经录入']);
        }
        $res = M("pay_url")->add(['order_no'=>$order_no,'url'=>$pay_url,'is_use'=>1,'amount'=>$amount,'create_time'=>time(),'create_date'=>date("Y-m-d H:i:s"),'status'=>1]);
        if($res)$this->ajaxReturn(['status'=>1,'msg'=>'success']);
        $this->ajaxReturn(['status'=>0,'msg'=>'save error']);
    }


    /**
     * 接口获取付款链接
     */
    public function get_pay_url(){
        $bc_order_no = I("post.order_no");
        $amount = I("post.amount");
        $notify_url = I("post.notify_url");
        /**
         * 验签
         * todo
         */
        $pay_model = M("pay_url");
        $pay_data = $pay_model->where(['amount'=>$amount,'status'=>1])->find();
        if(!$pay_data)$this->ajaxReturn(['status'=>0,'msg'=>'没有可用的支付链接']);
        $res = $pay_model->where(['id'=>$pay_data['id']])->save(['is_use'=>2,'bc_order_no'=>$bc_order_no,'pay_time'=>time(),'pay_date'=>date("Y-m-d H:i:s"),'notify_url'=>$notify_url]);
        if($res === false)$this->ajaxReturn(['status'=>0,'msg'=>'获取失败']);
        $redis = new \Redis();
        $redis->connect("127.0.0.1",6379);
        $redis->set("query_" .$bc_order_no,1);
        $this->ajaxReturn(['status'=>1,'msg'=>'success','pay_url'=>$pay_data['url']]);
    }


    public function query_order(){
        $redis = new \Redis();
        $redis->connect("127.0.0.1",6379);
        $keys = $redis->keys("*");
        $pay_model = M("pay_url");
        if($keys){
            foreach($keys as $key){
                if(strpos("query_",$key) !== false){
                    $bc_order_no = explode("_",$key)[1];
                    $order = $pay_model->where(['bc_order_no'=>$bc_order_no])->find();
                    $order_no = $order['order_no'];
                    $query_order = $this->get_order($order_no);
                    if($query_order){
                        if($query_order['status'] != 0){
                            $pay_model->where(['bc_order_no'=>$bc_order_no])->setField(['status'=>2]);
                            $return_data = [
                                'order_no'=>$bc_order_no,
                                'pay_status'=>2,
                                /**
                                 * 到账时间
                                 * todo
                                 */
                                'transferred_time'=>$order['到账时间']
                            ];
                            $res = curl_post($order['notify_url'],$return_data);
                            if($res != "success"){
                                $redis->set("error_order_".$bc_order_no,1);
                            }
                        }
                    }

                }
            }
        }


    }





    public function get_order($order_no){
        $url = "http://api.huiyuandao.com/token?shop_id=8004398&app_key=2111976&secret=651f5f5c78343c1d4b7a40dcce7acace";
        $access_token = json_decode(curl_get($url), true)['access_token'];
        $path = "http://api.huiyuandao.com/order/info?access_token=" . $access_token . "&order_no=" . $order_no;
        $order = json_decode(curl_get($path), true);
        if(isset($order['order_id'])){
            $data['order_no'] = $order['order_no'];// echo $path;
            $data['status'] = $order['status'];
            $data['amount'] = $order['payment'];
            return $data;
        }
        return false;

    }
}