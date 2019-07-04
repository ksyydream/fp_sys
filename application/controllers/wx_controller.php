<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wx_controller extends MY_Controller
{
    protected $wxconfig = array();
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        $this->load->model('sys_model');
        $this->load->helper('url');
        $this->wxconfig['appid']=$this->config->item('appid');
        $this->wxconfig['appsecret']=$this->config->item('appsecret');
        //var_dump($this->wxconfig);
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            $this->get_openid();
        }else{
            $openid = 'oFzKgwbFEyC40jU6bS_HQ5sxM4X8';
            $this->session->set_userdata('openid', $openid);
        }

        $res = $this->sys_model->check_openid($this->session->userdata('openid')); //通过openid绑定session
        //验证用户是否关注公众号
        //先看用户是否登录,如果已经登录了,就不去判断
        if($res != 1){
            $person_info = $this->getUserInfoById($this->session->userdata('openid'));
            if($person_info){
                if($person_info['subscribe'] != 1){
                    $this->load->library('wxjssdk_th',array('appid' => $this->config->item('appid'), 'appsecret' => $this->config->item('appsecret')));
                    $access_token = $this->wxjssdk_th->wxgetAccessToken();
                    $img_url = $this->get_or_create_ticket($access_token);
                    $this->cismarty->assign('img_url',$img_url);
                    $this->cismarty->display('estimate/wx_guanzhu.html');
                    exit();
                }
                //$s_p = $this->sys_model->save_person($person_info);
                if($person_info['qr_scene_str'] == 'person_info'){
                    //redirect('wx_index/person_info');
                }
            }else{
                $this->load->library('wxjssdk_th',array('appid' => $this->config->item('appid'), 'appsecret' => $this->config->item('appsecret')));
                $access_token = $this->wxjssdk_th->wxgetAccessToken();
                $img_url = $this->get_or_create_ticket($access_token);
                $this->cismarty->assign('img_url',$img_url);
                $this->cismarty->display('estimate/wx_guanzhu.html');
                exit();
            }
        }



    }

    /**
     * 获取微信的openid
     * @author yaobin <bin.yao@thmarket.cn>
     * @date 2017-12-27
     */
    public function get_openid(){
        if(!$this->session->userdata('openid')){
            $appid = $this->wxconfig['appid'];
            $secret = $this->wxconfig['appsecret'];
            if(empty($_GET['code'])){
                $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]; //这是要回调地址可以有别的写法
                redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$url}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect");
                //重定向到以上网址,这是微信给的固定地址.必须格式一致
            }else{
                //回调成功,获取code,再做请求,获取openid
                $j_access_token=file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$_GET['code']}&grant_type=authorization_code");
                $a_access_token=json_decode($j_access_token,true);
                $access_token=$a_access_token["access_token"];//虽然这里 也获取了一个access_token,但是和获取用户详情,还有发送模板信息所使用的access_token不同
                $openid=$a_access_token["openid"];
                $this->session->set_userdata('openid', $openid);
            }
        }
    }

    //重载smarty方法assign
    public function assign($key,$val) {
        $this->cismarty->assign($key,$val);
    }

    //重载smarty方法display
    public function display($html) {
        $this->cismarty->display($html);
    }

    public function set_base_code($token){
        require_once (APPPATH . 'libraries/Base64.php');
        try{
            $token = base64_decode($token);
            $token = base64::decrypt($token, $this->config->item('token_key'));
            $token = explode('_', $token);
            if($token[0]!= 'FIN') return -1;
            $t = time() - $token[2];
            if($t >= 60 * 60) return -2;
        }catch(Exception $e){
            return -3;
        }
        return (int)$token[1];
    }




    public function buildWxData(){
        $this->load->library('wxjssdk_th',array('appid' => $this->config->item('appid'), 'appsecret' => $this->config->item('appsecret')));
        $signPackage = $this->wxjssdk_th->wxgetSignPackage();
        //变量
        $this->cismarty->assign('wxappId',$signPackage["appId"]);
        $this->cismarty->assign('wxtimestamp',$signPackage["timestamp"]);
        $this->cismarty->assign('wxnonceStr',$signPackage["nonceStr"]);
        $this->cismarty->assign('wxsignature',$signPackage["signature"]);
    }

    public function getUserInfoById($uid, $lang = 'zh_CN') {
        $this->load->library('wxjssdk_th',array('appid' => $this->config->item('appid'), 'appsecret' => $this->config->item('appsecret')));
        $access_token = $this->wxjssdk_th->wxgetAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$uid&lang=$lang";
        $res = json_decode($this->request_post($url), true);
        $check_ = $this->checkIsSuc($res);

        if($check_){
            return $res;
        }else{
            return null;
        }
    }

    function request_post($url = '', $param = '')
    {
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }

    public function checkIsSuc($res) {
        $result = true;
        if (is_string($res)) {
            $res = json_decode($res, true);
        }
        if (isset($res['errcode']) && ( 0 !== (int) $res['errcode'])) {
            $result = false;
        }
        return $result;
    }

    private function get_or_create_ticket($access_token,$action_name = 'QR_SCENE') {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
        @$post_data->expire_seconds = 2592000;
        @$post_data->action_name = $action_name;
        $invite_code = $this->input->get('invite_code_temp');
        if(!isset($invite_code)){
            $invite_code = '';
        }
        @$post_data->action_info->scene->scene_str = $invite_code;
        $ticket_data = json_decode($this->post($url, $post_data));
        @$ticket = $ticket_data->ticket;
        $img_url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
        return $img_url;
    }

    private function post($url, $post_data, $timeout = 300){
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/json;encoding=utf-8',
                'content' => urldecode(json_encode($post_data)),
                'timeout' => $timeout
            )
        );
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

}