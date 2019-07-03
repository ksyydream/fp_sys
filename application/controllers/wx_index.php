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

        $ignore_methods = array(
            'logout',
            'submit_login',
            );
        if($this->session->userdata('wx_class') && !in_array($this->uri->segment(2), $ignore_methods)){
            /*if($this->session->userdata('wx_role_id') >= 1){
                redirect('wx_salesman');
                exit();
            }
            if($this->session->userdata('wx_role_id') <= -1){
                redirect('wx_customer');
                exit();
            }
            $this->logout();
            */
        }
    }


    public function index() {
        $this->display('login.html');
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