<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_index extends Wx_controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');
        $ignore_methods = array(
            'logout'
            );
        //判断是否存在 wx_class 的session,如果存在就说明有登录状态
        //同时也要判断是否是 退出操作
        if($this->session->userdata('wx_class') && !in_array($this->uri->segment(2), $ignore_methods)){
            //如果存在登录状态,又不是退出操作,就判断是哪种类型的用户,分别进入不同的控制器中
            if($this->session->userdata('wx_class') == 'users' && $this->session->userdata('wx_user_id') > 0){
                redirect('wx_users');
                exit();
            }
            if($this->session->userdata('wx_class') == 'members' && $this->session->userdata('wx_m_id') > 0){
                redirect('wx_members');
                exit();
            }
            $this->logout();

        }
    }


    public function index() {
        $this->display('login.html');
    }

    /**
     * 注册页面
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-03
     */
    public function register(){
        $invite_code = $this->input->get('invite_code_temp');
        $this->assign('invite_code', $invite_code);
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
        $person_info = $this->getUserInfoById($this->session->userdata('openid'));
        $data['pic'] = $person_info['headimgurl'];
        $res = $this->wx_index_model->reg_save($data);
        $this->ajaxReturn($res);


    }

    public function logout() {
        $this->wx_index_model->logout();
        redirect('wx_index/index');
    }

    public function user_login(){
        if(IS_POST){
            $data = $this->input->post();
            $this->wx_fail['msg'] = '信息丢失';
            if(!$data) {
                $this->ajaxReturn($this->wx_fail);
            }
            $res = $this->wx_index_model->user_login($data);
            $this->ajaxReturn($res);
        }
        $this->display('user_login.html');
    }

    public function member_login(){
        $this->display('admin_login.html');
    }

    public function test_RQimg($invite_code){
        $this->load->library('wxjssdk_th',array('appid' => $this->config->item('appid'), 'appsecret' => $this->config->item('appsecret')));
        $access_token = $this->wxjssdk_th->wxgetAccessToken();
        $img_url = $this->get_or_create_ticket($access_token, 'QR_STR_SCENE', $invite_code);
        $this->cismarty->assign('img_url',$img_url);
        $this->cismarty->display('estimate/wx_guanzhu.html');
    }


    public function begin_cal(){
        die('...');
        $res = $this->wx_index_model->begin_cal();
        if($res->success){
            $this->assign('data',$res);
            $this->display('get-credit.html');
        }else{
            $this->show_message('提交失败！');
        }
    }

    public function get_tongdun_info(){
        $res = $this->wx_index_model->get_tongdun_info($this->input->post('account_name'), $this->input->post('id_number'), $this->input->post('account_mobile'));
        $this->ajaxReturn($res);
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