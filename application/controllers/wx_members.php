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
        $this->m_info = $this->db->select()->from('members')->where('m_id', $this->m_id)->get()->row_array();
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


}