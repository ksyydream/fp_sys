<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_members extends Wx_controller {
    private $m_id;
    private $m_info = [];
    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $this->load->model('wx_members_model');
        $this->load->model('foreclosure_model');
        if($this->session->userdata('wx_class') != 'members' || !$this->session->userdata('wx_m_id') ){
            redirect('wx_index/logout');
        }
        $this->m_id = $this->session->userdata('wx_m_id');
        $this->m_info = $this->wx_members_model->get_member_info($this->m_id);
        if(!$this->m_info){
            redirect('wx_index/logout');
        }
        if($this->m_info['status'] != 1){
            redirect('wx_index/logout');
        }
    }


    public function index() {
        redirect('wx_members/foreclosure_list');
        //$this->display('users/index.html');
    }

    //赎楼列表
    public function foreclosure_list($status_type = 0){
        $this->assign('status_type', $status_type);
        $this->display('members/foreclosure/list1.html');
    }

    public function foreclosure_list_load(){
        $res = $this->foreclosure_model->get_list4members();
        //die(var_dump($res));
        $this->assign('list', $res);
        $this->display('members/foreclosure/list_data_load.html');
    }

    //检查 管理员 是否可以修改赎楼业务
    //只有总监可以修改和审核 , 也就是只有level是2的用户,并且是关联的总监才有权限
    private function check_foreclosure_edit($f_id = 0){
        //$fc_deadline_ = $this->config->item('fc_deadline'); //缓存数据使用限期,这里是秒为单位的
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        $m_info = $this->m_info;
        if(IS_POST){
            if(!$f_info){
                $res = $this->foreclosure_model->fun_fail('工作单不存在!');
                $this->ajaxReturn($res);
            }
            if($m_info['level'] != 2){
                $res = $this->foreclosure_model->fun_fail('只有总监级才有修改和审核权限!');
                $this->ajaxReturn($res);
            }
            //获取直接对接 业务员的信息
            $manger_info = $this->wx_members_model->get_member_info($f_info['m_id']);
            if($f_info['m_id'] != $m_info['m_id'] && $manger_info['parent_id'] != $m_info['m_id']){
                $res = $this->foreclosure_model->fun_fail('你没有权限修改,或审核此工作单!');
                $this->ajaxReturn($res);
            }
            if($f_info['status'] != 2){
                $res = $this->foreclosure_model->fun_fail('工作单已不再待审核内,不可修改!');
                $this->ajaxReturn($res);
            }
        }else{
            if(!$f_info){
                redirect('wx_members/foreclosure_list'); //不是自己的工作单,就直接回到首页
            }
            if($m_info['level'] != 2){
                redirect('wx_members/foreclosure_list'); //不是自己的工作单,就直接回到首页
            }
            //获取直接对接 业务员的信息
            $manger_info = $this->wx_members_model->get_member_info($f_info['m_id']);
            if($f_info['m_id'] != $m_info['m_id'] && $manger_info['parent_id'] != $m_info['m_id']){
                redirect('wx_members/foreclosure_list'); //不是自己的工作单,就直接回到首页
            }
            if($f_info['status'] != 2){
                redirect('wx_members/foreclosure_list'); ////如果工作单不是待审核,就到详情页面
            }
        }
        return true;
    }

    /**
     * 申请赎楼 借款人信息
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function foreclosure_show($f_id = 0){
        $this->check_foreclosure_edit($f_id); //检查权限
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        $this->assign('f_info', $f_info);
        $this->assign('now_time', $f_info['now_time']);
        $this->display('members/foreclosure/step_show.html');
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
        $this->display('members/foreclosure/step2.html');
    }

    /**
     * 申请赎楼 贷款信息
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-12
     */
    public function foreclosure_s3($f_id = 0){

        if(IS_POST){
            $f_id = $this->input->post('fc_id');
            $this->check_foreclosure_edit($f_id); //检查权限
            $res = $this->foreclosure_model->edit_foreclosure4s3();
            $this->ajaxReturn($res);
        }
        $this->check_foreclosure_edit($f_id); //检查权限
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        if($f_info['bank_loan_type'] == 1){
            //redirect('wx_users/foreclosure_s4/' . $f_id); //如果是一次性付款 不需要填写此页面
        }
        $this->assign('f_info', $f_info);
        $this->display('members/foreclosure/step3.html');
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
        $this->display('members/foreclosure/step4.html');
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
        $this->buildWxData();
        $this->assign('f_info', $f_info);
        $this->assign('property_img_list', $property_img_list);
        $this->display('members/foreclosure/step5.html');
    }

    /**
     * 申请赎楼 上传征信报告
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function foreclosure_s6($f_id = 0){
        if(IS_POST){
            $f_id = $this->input->post('fc_id');
            $this->check_foreclosure_edit($f_id); //检查权限
            $res = $this->foreclosure_model->edit_foreclosure4s6();
            $this->ajaxReturn($res);
        }
        $this->check_foreclosure_edit($f_id); //检查权限
        $f_info = $this->foreclosure_model->get_foreclosure($f_id);
        $credit_img_list = $this->foreclosure_model->get_credit_img($f_id);
        $this->buildWxData();
        $this->assign('f_info', $f_info);
        $this->assign('credit_img_list', $credit_img_list);
        $this->display('members/foreclosure/step6.html');
    }


}