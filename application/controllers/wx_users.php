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
     * 注册页面
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-03
     */
    public function register(){
        $this->display('register.html');
    }

    /**
     * 注册申请
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-03
     */
    public function reg_save(){
        $data = $this->input->post();
        $this->wx_fail['msg'] = '信息丢失';
        if(!$data) {
            $this->ajaxReturn($this->wx_fail);
        }
        $res = $this->wx_index_model->reg_save($data);
        $this->ajaxReturn($res);
        if(!$data['rel_name']){
            $this->wx_fail['msg'] = '姓名不能为空!';
            $this->ajaxReturn($this->wx_fail);
        }
        if(!$data['mobile']){
            $this->wx_fail['msg'] = '手机号不能为空!';
            $this->ajaxReturn($this->wx_fail);
        }
        if(!$data['code']){
            $this->wx_fail['msg'] = '短信验证码不能为空!';
            $this->ajaxReturn($this->wx_fail);
        }
        if(!$data['invite_code']){
            $this->wx_fail['msg'] = '邀请码不能为空!';
            $this->ajaxReturn($this->wx_fail);
        }
        switch($data['type']){
            case 1:
                break;
            case 2:
                break;
            default:
                $this->wx_fail['msg'] = '请选择注册类型';
                $this->ajaxReturn($this->wx_fail);
        }

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


    public function main(){
        $this->display('index.html');
    }


    public function begin_cal(){
        $res = $this->wx_index_model->begin_cal();
        if($res->success){
            $this->assign('data',$res);
            $this->display('get-credit.html');
        }else{
            $this->show_message('提交失败！');
        }
    }

    public function test_gkd(){
        //$data = mb_convert_encoding("1灏忔椂鍐呰韩浠借瘉鎴栨墜鏈哄彿鐢宠娆℃暟澶т簬绛変簬3", "UTF-8", "GBK");
        header("Content-Type: text/html;charset=utf-8");
        $old = "1灏忔椂鍐呰韩浠借瘉鎴栨墜鏈哄彿鐢宠娆℃暟澶т簬绛変簬3";
        //echo $old;
        $data =iconv("gbk", "utf-8//ignore", "1灏忔椂鍐呰韩浠借瘉鎴栨墜鏈哄彿鐢宠娆℃暟澶т簬绛変簬3");
        $str = mb_convert_encoding("1灏忔椂鍐呰韩浠借瘉鎴栨墜鏈哄彿鐢宠娆℃暟澶т簬绛変簬3", "utf-8", "gbk");
        $res = iconv("utf-8", "gb2312//ignore", $old);
        //echo $data;
        //echo $str;
        echo $res;
    }
}