<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/2/16
 * Time: 09:56
 */

 if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "wx_controller.php";
class Wx_api extends CI_controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('wx_index_model');

    }

    public function get_zcs(){
        $data = $this->wx_index_model->get_zcs();
        echo json_encode($data);
    }

}