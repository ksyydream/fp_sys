<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_salesman extends Wx_controller {

    private $role_id = 0;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $this->load->model('wx_salesman_model');
        if(!$this->session->userdata('wx_user_id')){
            redirect('wx_index');
        }
        $this->role_id = $this->session->userdata('wx_role_id');
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('wx_user_id')) {
            redirect('wx_index');
        } else {
            switch ($this->role_id){
                case 1:
                    //管理员
                    break;
                case 2:
                    //公司经理
                    break;
                case 3:
                    //行政组
                    break;
                case 4:
                    //财务组
                    break;
                case 5:
                    //风控组
                    break;
                case 6:
                    //权证组
                    break;
                case 7:
                    //业务组
                    break;
                case -1:
                    //渠道客户
                    if($method == 'add_customer'
                        || $method == 'customer_list'
                        || $method == 'customer_save'
                    ){
                        redirect(site_url('/wx_salesman/index'));
                        exit();
                    }
                    break;
                default:
                    redirect('wx_index');
                    break;
            }
            return call_user_func_array(array($this, $method), $params);
        }
    }



    public function index() {
        $this->display('salesman/index.html');
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

    public function add_customer() {
        $this->display('salesman/add_company.html');
    }

    public function edit_customer($id) {

        $this->display('salesman/add_company.html');
    }

    public function save_customer() {
        $rs = $this->wx_salesman_model->save_customer();
        if($rs == 1){
            $this->show_message('保存成功',site_url('wx_salesman/customer_list'));
        }else if($rs == -2){
            $this->show_message('信息缺失！');
        }else if($rs == -3){
            $this->show_message('渠道公司已满！');
        }else{
            $this->show_message('保存失败！');
        }
    }

    public function customer_list() {
        $data = $this->wx_salesman_model->customer_list();
        $this->assign('data',$data);
        $this->assign('pagination_url','/wx_salesman/customer_list/');
        $this->display('salesman/qy_company.html');
    }

}