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
    private $is_manager = 0;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $this->load->model('wx_salesman_model');
        if(!$this->session->userdata('wx_user_id')){
            redirect('wx_index');
        }
        $this->role_id = $this->session->userdata('wx_role_id');
        if($this->role_id <= -1){
            redirect('wx_customer');
        }
        $this->is_manager = $this->session->userdata('wx_is_manager') ? $this->session->userdata('wx_is_manager') : -1;
        $this->assign('is_manager',$this->is_manager);
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
                default:
                    redirect('wx_customer');
                    break;
            }
            return call_user_func_array(array($this, $method), $params);
        }
    }



    public function index() {
        $this->display('salesman/index.html');
    }

    public function add_customer() {
        $this->display('salesman/add_company.html');
    }

    public function edit_customer($id) {
        $customer = $this->wx_salesman_model->get_customer($id);
        if(!$customer){
            $this->show_message('未找到渠道公司信息！');
        }
        $user_7_list = $this->wx_salesman_model->get_user_7_list();
        $this->assign('user_7_list',$user_7_list);
        $this->assign('customer',$customer);
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