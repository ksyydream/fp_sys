<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
class Wx_salesman_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 修改密码
     */
    public function save_change_pwd(){
        $this->db->where('id',$this->session->userdata('wx_user_id'));
        $rs = $this->db->update('user',array('password'=>sha1($this->input->post('new_passwd'))));
        if($rs)
            return true;
        else
            return false;
    }
}