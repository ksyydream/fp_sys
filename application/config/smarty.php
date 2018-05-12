<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');  
$config['cache_lifetime']  = 30*24*3600; //更新周期
$config['caching']         = false;//是否使用缓存，项目在调试期间，不建议启用缓存
$config['template_dir']    = APPPATH.'views'; //设置模板目录
$config['compile_dir']     = APPPATH.'views/template_c'; //设置编译目录
$config['cache_dir']       = APPPATH.'views/cache';//缓存文件夹
$config['use_sub_dirs']    = true;   //子目录变量（是否在缓存文件夹中生成子目录）
$config['left_delimiter']  = '{{';
$config['right_delimiter'] = '}}';
?>