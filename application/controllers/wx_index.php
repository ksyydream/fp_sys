<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_index extends Wx_controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $this->load->model('wx_salesman_model');
        $ignore_methods = array(
            'logout',
            'change_pwd',
            'save_change_pwd',
            'submit_login',
            'main',
            'calculator',
            'calculator_res',
            'api_get_xiaoqu_list',
            'person_info',
            'pg_list_new');
        if($this->session->userdata('wx_user_id') && !in_array($this->uri->segment(2), $ignore_methods)){
            /*if($this->session->userdata('wx_role_id') >= 1){
                redirect('wx_salesman');
                exit();
            }
            if($this->session->userdata('wx_role_id') <= -1){
                redirect('wx_customer');
                exit();
            }
            $this->logout();
            */
        }
    }


    public function index() {
        $this->display('login.html');
    }

    /**
     * 登陆提交
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2018-05-13
     */
    public function submit_login(){
        $rs = $this->wx_index_model->submit_login();
        if($rs == '1'){
            $this->show_message('登陆成功',site_url('wx_salesman/index'));
        }else if($rs == '-1'){
            $this->show_message('登陆失败！');
        }else{
            $this->show_message('登陆失败！');
        }
    }

    public function logout() {
        $this->wx_index_model->logout();
        redirect('wx_index/index');
    }

    public function change_pwd() {
        $this->display('salesman/change_pwd.html');
    }

    public function save_change_pwd() {
        if(sha1($this->input->post('passwd')) != $this->session->userdata('wx_password')){
            $this->show_message('原密码错误！');
        }else{
            $rs = $this->wx_salesman_model->save_change_pwd();
            if($rs){
                $this->show_message('修改成功',site_url('wx_salesman/index'));
            }else{
                $this->show_message('修改失败！');
            }
        }
    }

    public function main(){
        $this->display('index.html');
    }

    public function pg_list_new(){
        $this->display('estimate/estimate-new.html');
    }

    public function person_info(){
        $data = $this->sys_model->person_info();
        $this->assign('data',$data);
        $this->display('estimate/user.html');
    }


    public function api_get_xiaoqu_list(){
        $data = $this->wx_index_model->api_get_xiaoqu_list();
        echo json_encode($data);
    }


       public function calculator($max_price = 0, $min_price = 0){
//            $data = $this->wx_index_model->calculator();
//            $this->assign('data',$data);
           $this->assign('max_price',$max_price);
           $this->assign('min_price',$min_price);
            $this->assign('pagination_url','/wx_index/calculator/');
            $this->display('estimate/calculator.html');
        }

    public function calculator_res(){
        $data = $this->input->get();
        $this->assign('data',$data);
        $this->display('estimate/calculator_res.html');
    }
}