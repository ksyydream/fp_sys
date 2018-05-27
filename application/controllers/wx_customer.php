<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_customer extends Wx_controller {

    private $role_id = 0;
    private $is_manager = 0;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $this->load->model('wx_salesman_model');
        $this->load->model('wx_customer_model');
        if(!$this->session->userdata('wx_user_id')){
            redirect('wx_index');
        }
        $this->role_id = $this->session->userdata('wx_role_id');
        if($this->role_id >= 1){
            redirect('wx_salesman');
        }
        $this->is_manager = $this->session->userdata('wx_is_manager') ? $this->session->userdata('wx_is_manager') : -1;
        $this->assign('is_manager',$this->is_manager);
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('wx_user_id')) {
            redirect('wx_index');
        } else {
            switch ($this->role_id){
                case -2:
                    //渠道客户子账户
                    break;
                case -1:
                    break;
                default:
                    redirect('wx_index');
                    break;
            }
            return call_user_func_array(array($this, $method), $params);
        }
    }



    public function index() {
        $this->display('customer/index.html');
    }

    public function list_ywy() {
        $data = $this->wx_customer_model->list_ywy();
        $this->assign('data',$data);
        $this->display('customer/list_ywy.html');
    }

    public function save_ywy(){
        $rs = $this->wx_customer_model->save_ywy();
        if($rs == '1'){
            $this->show_message('保存成功',site_url('wx_customer/list_ywy'),'1');
        }else if($rs == '-1'){
            $this->show_message('改账号已经存在！','','1');
        }else{
            $this->show_message('保存失败','','1');
        }
    }

}