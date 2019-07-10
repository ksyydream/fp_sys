<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_users extends Wx_controller {
    private $user_id;
    private $user_info = [];
    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $this->load->model('wx_users_model');
        if($this->session->userdata('wx_class') != 'users' || !$this->session->userdata('wx_user_id') ){
            redirect('wx_index/logout');
        }
        $this->user_id = $this->session->userdata('wx_user_id');
        $this->user_info = $this->wx_users_model->get_user_info($this->user_id);
        if(!$this->user_info){
            redirect('wx_index/logout');
        }
        if($this->user_info['status'] != 1){
            redirect('wx_index/logout');
        }
    }


    public function index() {
        $this->display('users/index.html');
    }


    /**
     * 申请赎楼一
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-09
     */
    public function foreclosure($now_time = ''){
        if(IS_POST){
            $res = $this->wx_users_model->save_foreclosure($this->user_info);
            $this->ajaxReturn($res);
        }
        if(!$now_time){
            //以防返回重复生成工作单,需要在所有入口增加随机数进行判断,如果不存在 就是非法入口,需要跳转到别的地方
            redirect('wx_users/foreclosure/' . time()); //自动增加now_time
        }
        $this->assign('now_time', $now_time);
        $this->display('users/foreclosure/step1.html');
    }

    /**
     * 申请赎楼 第二步,填写主贷人和类型
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function foreclosure_s2($f_id = 0){
        if(IS_POST){
            $res = $this->wx_users_model->edit_foreclosure4s2();
            $this->ajaxReturn($res);
        }
        $f_info = $this->wx_users_model->get_foreclosure4user($f_id);
        if(!$f_info || $f_info['status'] != 1){
            redirect('wx_users/index'); //当发现工作单不能修改时,应该会进入列表,但因为现在没有,所以先进入首页
        }
        $this->assign('f_info', $f_info);
        $this->display('users/foreclosure/step2.html');
    }

    /**
     * 申请赎楼 上次身份证
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function foreclosure_s4($f_id){
        $f_info = $this->wx_users_model->get_foreclosure4user($f_id);
        if(!$f_info || $f_info['status'] != 1){
            redirect('wx_users/index'); //当发现工作单不能修改时,应该会进入列表,但因为现在没有,所以先进入首页
        }
        if(IS_POST){
            $res = $this->wx_users_model->edit_foreclosure4();
            $this->ajaxReturn($res);
        }
        $this->assign('f_info', $f_info);
        $this->display('users/foreclosure/step4.html');
    }
}