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
        $this->load->model('foreclosure_model');
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
        $this->assign('controller_name', 'wx_users');
    }


    public function index() {
        $this->display('users/index.html');
    }

    //检查用户是否可以修改赎楼业务
    private function check_foreclosure_edit($f_id = 0){
        $fc_deadline_ = $this->config->item('fc_deadline'); //缓存数据使用限期,这里是秒为单位的
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        if(IS_POST){
            if(!$f_info || $f_info['user_id'] != $this->user_id){
                $res = $this->foreclosure_model->fun_fail('工作单不存在!');
                $this->ajaxReturn($res);
            }
            if($f_info['status'] != 1){
                $res = $this->foreclosure_model->fun_fail('工作单已不再草稿箱内,不可修改!');
                $this->ajaxReturn($res);
            }

            if($f_info['add_time'] + $fc_deadline_ < time()){
                $res = $this->foreclosure_model->fun_fail('工作单已过有效期!,不可修改');
                $this->ajaxReturn($res);
            }
        }else{
            if(!$f_info || $f_info['user_id'] != $this->user_id){
                redirect('wx_users/index'); //不是自己的工作单,就直接回到首页
            }
            if($f_info['status'] != 1){
                redirect('wx_users/index'); //如果工作单不在草稿箱内,或者已经过期,就到详情页面
            }
            if($f_info['add_time'] + $fc_deadline_ < time()){
                redirect('wx_users/index'); //如果工作单不在草稿箱内,或者已经过期,就到详情页面
            }
        }
        return true;
    }

    /**
     * 申请赎楼一
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-09
     */
    public function foreclosure($now_time = ''){
        if(IS_POST){
            $res = $this->foreclosure_model->save_foreclosure($this->user_info);
            $this->ajaxReturn($res);
        }
        if(!$now_time){
            //以防返回重复生成工作单,需要在所有入口增加随机数进行判断,如果不存在 就是非法入口,需要跳转到别的地方
            redirect('wx_users/foreclosure/' . $this->user_id . '_' . time()); //自动增加now_time
        }else{
            $f_info = $this->foreclosure_model->get_foreclosureBynowtime($now_time);
            //判断now_time是否存在工作单,只要存在,但不符合条件就应该当做是一个新的申请
            if($f_info){
                $fc_deadline_ = $this->config->item('fc_deadline'); //缓存数据使用限期,这里是秒为单位的
                if($f_info['user_id'] == $this->user_id && $f_info['status'] == 1 && $f_info['add_time'] + $fc_deadline_ > time()) {
                    $this->assign('f_info', $f_info);
                    $this->assign('now_time', $now_time);
                    $this->display('users/foreclosure/step_show.html');
                }else{
                    redirect('wx_users/foreclosure/' . $this->user_id . '_' . time()); //自动增加now_time
                }
            }else{
                $this->assign('f_info', array());
            }
        }
        $this->assign('now_time', $now_time);
        $this->display('users/foreclosure/step1.html');
    }

    //同盾审核提示页面
    public function foreclosure_td($f_id = 0){
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        $this->check_foreclosure_edit($f_id); //检查权限
        $this->assign('f_info', $f_info);
        //查看总体同盾审核情况
        if($f_info['td_status'] == 2){
            $this->display('users/foreclosure/result_ok.html');
        }else{
            $this->display('users/foreclosure/result_fail.html');
        }


    }

    /**
     * 申请赎楼 第二步,填写主贷人和类型
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function foreclosure_s2($f_id = 0){

        if(IS_POST){
            $f_id = $this->input->post('fc_id');
            $this->check_foreclosure_edit($f_id); //检查权限
            $res = $this->foreclosure_model->edit_foreclosure4s2();
            $this->ajaxReturn($res);
        }
        $this->check_foreclosure_edit($f_id); //检查权限
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        $this->assign('f_info', $f_info);
        $this->display('users/foreclosure/step2.html');
    }

    /**
     * 申请赎楼 上传身份证
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function foreclosure_s4($f_id = 0){
        if(IS_POST){
            $f_id = $this->input->post('fc_id');
            $this->check_foreclosure_edit($f_id); //检查权限
            $res = $this->foreclosure_model->edit_foreclosure4s4();
            $this->ajaxReturn($res);
        }
        $this->check_foreclosure_edit($f_id); //检查权限
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        $this->buildWxData();
        $this->assign('f_info', $f_info);
        $this->display('users/foreclosure/step4.html');
    }

    /**
     * 申请赎楼 上传房产证
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function foreclosure_s5($f_id = 0){
        if(IS_POST){
            $f_id = $this->input->post('fc_id');
            $this->check_foreclosure_edit($f_id); //检查权限
            $res = $this->foreclosure_model->edit_foreclosure4s5();
            $this->ajaxReturn($res);
        }
        $this->check_foreclosure_edit($f_id); //检查权限
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        $property_img_list = $this->foreclosure_model->get_property_img($f_id);
        //$this->buildWxData();
        $this->assign('f_info', $f_info);
        $this->assign('property_img_list', $property_img_list);
        $this->display('users/foreclosure/step5.html');
    }
}