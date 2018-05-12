<?php
if (!defined('BASEPATH')) exit("no direct script access allowd");  
//以下是加载smarty的类文件   
require_once(APPPATH.'libraries/smarty/Smarty.class.php');  
//定义cismarty类，继承smarty类   
class cismarty extends Smarty{  
    //定义一个受保护的变量,   
    protected $ci;  
  
    function __construct(){  
        parent::__construct();  
        //引用实例化CI,这里主要是将smarty的配置文件写到ci中，以方便程序管理   
        $this->ci = & get_instance();  
        //加载ci的新建的smarty配置文件   
        $this->ci->load->config('smarty');  
        $this->cache_lifetime  = $this->ci->config->item('cache_lifetime');  
        $this->caching         = $this->ci->config->item('caching');  
        $this->template_dir    = $this->ci->config->item('template_dir');  
        $this->compile_dir     = $this->ci->config->item('compile_dir');  
        $this->cache_dir       = $this->ci->config->item('cache_dir');  
        $this->use_sub_dirs    = $this->ci->config->item('use_sub_dirs');  
        $this->left_delimiter  = $this->ci->config->item('left_delimiter');  
        $this->right_delimiter = $this->ci->config->item('right_delimiter');  
    }
}
?>