<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_api extends CI_controller {

    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        $this->load->model('wx_index_model');

    }

    public function get_zcs(){
        $data = $this->wx_index_model->get_zcs();
        echo json_encode($data);
    }

    public function api_get_xiaoqu_info(){
        if($this->session->userdata('openid')){
            $data = $this->wx_index_model->api_get_xiaoqu_info();
            echo json_encode($data);
        }
    }
    public function api_get_price4jq(){
        if($this->session->userdata('openid')){
            $data = $this->wx_index_model->api_get_price4jq();
            echo json_encode($data);
        }
    }

    public function api_user_info(){
        if($this->session->userdata('openid')){
            $data = $this->wx_index_model->api_user_info();
            echo json_encode($data);
        }
    }
}