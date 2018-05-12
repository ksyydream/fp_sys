<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 后台画面控制器
 *
 * @package		app
 * @subpackage	core
 * @category	controller
 * @author		yaobin<645894453@qq.com>
 *
 */
class Manage_login extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('manage_model');

	}
	
    
    //登陆
    public function login(){
    	if(!$this->input->is_ajax_request()){
    		$this->load->view('manage/login');
        }else{
            $this->load->view('manage/login_dialog');
        }
    }
    
    /**
     * 检验登录
     * 
     * 初始登录时，跳转到系统页
     * 中途过期登录时，ajax方式提示
     */
    public function check_login()
    {
        if($this->input->is_ajax_request()){
            if($this->manage_model->check_login()){
                form_submit_json("200", "操作成功");
            }else{
                form_submit_json("300", "操作失败");
            }
        }else{
            session_start();
            $this->load->library('form_validation');
            $this->form_validation->set_rules('username', '用户名', 'required|max_length[20]');
            $this->form_validation->set_rules('password', '密码', 'required');
            
            if ($this->form_validation->run() == FALSE)
            {
                $this->load->view('login/login');
            }else{
                if($this->manage_model->check_login()){
                    redirect(site_url('manage/index'));
                }else{
                    $data['login_errors'] = '登录失败,请核实登录信息!';
                    $this->load->view('manage/login',$data);
                }
            }
        }
    }
    
    /**
     * 修改密码
     */
    public function change_pwd(){
    	if($this->input->post())
			if ($this->manage_model->check_login()){
				$rs = $this->manage_model->change_pwd();
				if($rs == 1){
					form_submit_json("200", "操作成功");
				}else{
					form_submit_json("300", $rs);
				}
			}else{
				form_submit_json("300", "旧密码不正确！");
			}
		else 
			$this->load->view('manage/changepwd.php');
    }
    
    
    /**
     * 退出登录，并定向到登录页
     */
    public function logout(){
        $this->session->sess_destroy();
        redirect(site_url('manage/login'));
    }
}
