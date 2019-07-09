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
        $this->user_info = $this->wx_users_model->get_user_info($this->user_id);
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
     * 申请赎楼一
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-09
     */
    public function foreclosure(){
        if(IS_POST){
            $res = $this->wx_users_model->save_foreclosure($this->user_info);
            $this->ajaxReturn($res);
            $insert_ = array(
                'borrower_marriage' => $this->input->post('is_marriage'),
                'borrower_name' => $this->input->post('borrower_name'),
                'borrower_code' => $this->input->post('borrower_code'),
                'borrower_mobile' => $this->input->post('borrower_mobile'),
                'now_time' => $this->input->post('now_time'),
                'user_id' => $this->user_id,
                'add_time' => time(),
                'status' => 1,
            );
            //开始效验
            switch($insert_['borrower_marriage']){
                case 1:
                    break;
                case -1:
                    break;
                default:

            }
        }
        $this->assign('now_time', time());
        $this->display('users/foreclosure/step1.html');
    }

}