<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * 扩展业务控制器
 *
 * @package		app
 * @subpackage	Libraries
 * @category	controller
 * @author      yaobin<645894453@qq.com>
 *        
 */
class MY_Controller extends CI_Controller
{

    public function __construct ()
    {
        parent::__construct();
		$this->load->model('sys_model');
        ini_set('date.timezone','Asia/Shanghai');
        $this->cismarty->assign('base_url',base_url());//url路径
		$login_user_id = $this->session->userdata('login_user_id');
		if($login_user_id > 0){
			$data = $this->sys_model->get_token($login_user_id);
			if($data != -1){
				if($data != $this->session->userdata('login_token')){
					$this->session->unset_userdata('login_user_id');
					$login_user_id = 0;
					}
			}
		}
		if ($login_user_id <=0 || !$this->session->userdata('login_user_id')){
			if($this->uri->segment(2) == 'popup_room'){
				die('账户登陆超时或在异地登陆,请退出后再登陆');
			}
			if($this->uri->segment(2) == 'show_room'){
				die('账户登陆超时或在异地登陆,请退出后再登陆');
			}
			if($this->uri->segment(2) == 'show_code'){
				die('账户登陆超时或在异地登陆,请退出后再登陆');
			}
			if($this->uri->segment(2) == 'zysm'){
				die('-1');
			}
		}
		//如果是评估管理员 进入非index/manage/pg控制器, 就返回首页
		if(!$this->session->userdata('login_user_id')){
			$user_position_arr_ = array();
			$user_position_arr_ =$this->session->userdata('login_position_id_array')?$this->session->userdata('login_position_id_array'):array();
			if(in_array(11,$user_position_arr_)){
				$class_arr_ = array("index","manage","pg");
				$user_class = $this->router->class;
				if(!in_array($user_class,$class_arr_)){
					redirect(site_url('/'));
					exit();
				}
			}
		}

		$this->cismarty->assign('login_user_id', $login_user_id > 0 ? true : false);
		$this->cismarty->assign('login_user_name', $this->session->userdata('login_rel_name'));
		$this->cismarty->assign('login_flag', $this->session->userdata('login_flag') ? $this->session->userdata('login_flag') : -1);
		$user_pic = $this->session->userdata('login_user_pic');
		if(empty($user_pic)) $user_pic = 'user_photo.gif';
		if(!@file_get_contents('./uploadfiles/profile/'.$user_pic)){
			$user_pic='user_photo.gif';
		}
		$this->cismarty->assign('login_user_pic', $user_pic);
    }
    
	//重载smarty方法assign
	public function assign($key,$val) {  
        $this->cismarty->assign($key,$val);  
    }  
    
	//重载smarty方法display
    public function display($html) {
        $this->cismarty->display($html);  
    }
    
    /**
     * 获取产品菜单的树状结构
     **/
    public function subtree($arr,$id=0,$lev=1)
    {
    	static $subs = array();
    	foreach($arr as $v){
    		if((int)$v['parent_id']==$id){
    		    $v['lev'] = $lev;
    		    $subs[]=$v;
    		    $this->subtree($arr,$v['id'],$lev+1);
    		}
    	}
    	return $subs;
    }
    
    /**
     * 提示信息
     * @param varchar $message 提示信息
     * @param varchar $url 跳转页面，如果为空则后退
     * @param int $type 提示类型，1是自动关闭的提示框，2是错误提示框
     **/
    public function show_message($message,$url=null,$type=1){
    	if($url){
    		$js = "location.href='".$url."';";
    	}else{
    		$js = "history.back();";
    	}
    
    	if($type=='1'){
    		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
				<html xmlns='http://www.w3.org/1999/xhtml'>
				<head>
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
				<title>".$message."</title>
				<script src='".base_url()."js/jquery.min.js'></script>
				<link rel='stylesheet' href='".base_url()."css/easydialog.css'>
				</head>
				<body>
				<script src='".base_url()."js/easydialog.min.js'></script>
				<script>
				var callFn = function(){
				  ".$js."
				};
				easyDialog.open({
					container : {
						content : '".$message."'
					},
					autoClose : 1200,
					callback : callFn
			
				});
    
				</script>
				</body>
				</html>";
    	}
    	exit;
    }
    
    /**
     * 中国正常GCJ02坐标---->百度地图BD09坐标
     * 腾讯地图用的也是GCJ02坐标
     * @param double $lat 纬度
     * @param double $lng 经度
     */
    function Convert_GCJ02_To_BD09($lat,$lng){
    	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    	$x = $lng;
    	$y = $lat;
    	$z =sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
    	$theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
    	$lng = $z * cos($theta) + 0.0065;
    	$lat = $z * sin($theta) + 0.006;
    	return array('lng'=>$lng,'lat'=>$lat);
    }
    
    
    private function createNonceStr($length = 16) {
    	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    	$str = "";
    	for ($i = 0; $i < $length; $i++) {
    		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    	}
    	return $str;
    }
    
    function getSignPackage($ticket) {
    	$jsapiTicket = $ticket['ticket'];
    	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    	$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    	$timestamp = time();
    	$nonceStr = $this->createNonceStr();
    	$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    	$signature = sha1($string);
    	$signPackage = array(
    			"appId"     => APP_ID,
    			"nonceStr"  => $nonceStr,
    			"timestamp" => $timestamp,
    			"url"       => $url,
    			"signature" => $signature,
    			"rawString" => $string
    	);
    	return $signPackage;
    }
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */