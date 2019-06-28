<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/2/16
 * Time: 21:22
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Agency extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
    }

    //重载smarty方法assign
    public function assign($key,$val) {
        $this->cismarty->assign($key,$val);
    }

    //重载smarty方法display
    public function display($html) {
        $this->cismarty->display($html);
    }

    public function inquiry(){
        $this->display('agency/index.html');
    }

    public function test_sms(){
        $this->load->model('sms_model');
        $ali_templateCode = $this->config->item('ali_templateCode');
        $res = $this->sms_model->sendSmsByAliyun('18914970292', '房猫服务中心', '8888', $ali_templateCode['1']);
        die(var_dump($res));
    }

    public function sendSms(){
        $this->load->model('sms_model');
        $ali_templateCode = $this->config->item('ali_templateCode');
        $res = $this->sms_model->sendSms('18914970292', '房猫服务中心', '1111', $ali_templateCode['1']);
        die(var_dump($res));
    }
}

