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

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $this->load->model('wx_salesman_model');
        if(!$this->session->userdata('wx_user_id')){
            redirect('wx_index');
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

}