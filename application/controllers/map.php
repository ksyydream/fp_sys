<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/2/16
 * Time: 21:22
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Map extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        $this->load->model('map_model');
    }

    //重载smarty方法assign
    public function assign($key,$val) {
        $this->cismarty->assign($key,$val);
    }

    //重载smarty方法display
    public function display($html) {
        $this->cismarty->display($html);
    }

    public function map_show(){

        $data = $this->map_model->get_map_info();
        //die(var_dump($data));
        $this->assign('data', $data);
        $this->display("map/map_main.html");
    }
}

