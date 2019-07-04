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
        $this->user_info = $this->db->select()->from('users')->where('user_id', $this->user_id)->get()->row_array();
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

}