<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
class Wx_index_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function logout(){
        $this->db->where('id',$this->session->userdata('wx_user_id'))->update('user',array('openid'=>''));
        $this->session->unset_userdata('wx_user_id');
        $this->session->sess_destroy();
    }

    public function submit_login(){
        $openid = $this->session->userdata('openid');
        $username = $this->input->post('name');
        $password = $this->input->post('passwd');
        $this->db->from('user');
        $this->db->where('username', $username);
        $this->db->where('password', sha1($password));
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $this->db->where('openid',$openid)->set('openid','')->update('user');
            $res = $rs->row();
            $this->db->where('id',$res->id)->update('user',array('openid'=>$openid));
            return 1;
        }
        return -1;
    }
}