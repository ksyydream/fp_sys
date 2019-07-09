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
        echo 'Member';
        //$this->display('users/index.html');
    }




}