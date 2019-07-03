<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
class Sys_model extends MY_Model
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
     * 检查token
     *
     * @return boolean
     */
    public function get_token($id){
       $row = $this->db->select()->from('users')->where('id',$id)->get()->row_array();
        if (!$row){
            return -1;
        }else{
            return $row['token'];
        }
    }

    public function check_person(){

    }

    public function check_openid(){
        $openid = $this->session->userdata('openid');
        //检测是否是users
        $this->db->select('a.user_id,a.rel_name')->from('users a');
        $this->db->where('a.openid',$openid);
        $row = $this->db->get()->row_array();
        if($row){
            $res = $this->set_user_session_wx($row['user_id']);
            if($res==1)
                return 1;
        }
        $this->session->unset_userdata('wx_user_id');
        //检测是否是members
        $this->db->select('a.m_id,a.rel_name')->from('members a');
        $this->db->where('a.openid',$openid);
        $row = $this->db->get()->row_array();
        if($row){
            $res = $this->set_member_session_wx($row['m_id']);
            if($res==1)
                return 1;
        }
        $this->session->unset_userdata('wx_m_id');
        $this->session->unset_userdata('wx_class');
        return -1;
    }

    public function set_user_session_wx($id){
        $this->db->from('users');
        $this->db->where('user_id', $id);
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $res = $rs->row();
            $token = uniqid();
            $user_info['wx_token'] = $token;
            $user_info['wx_user_id'] = $res->user_id;
            $user_info['wx_rel_name'] = $res->rel_name;
            $user_info['wx_user_pic'] = $res->pic;
            $user_info['wx_class'] = 'users';
            $this->session->set_userdata($user_info);
            return 1;
        }
        return -1;
    }

    public function set_member_session_wx($id){
        $this->db->from('members');
        $this->db->where('m_id', $id);
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $res = $rs->row();
            $token = uniqid();
            $member_info['wx_token'] = $token;
            $member_info['wx_m_id'] = $res->m_id;
            $member_info['wx_rel_name'] = $res->rel_name;
            $member_info['wx_user_pic'] = $res->pic;
            $member_info['wx_class'] = 'members';
            $this->session->set_userdata($member_info);
            return 1;
        }
        return -1;
    }

    public function check_user(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('users')->where('openid', $openid)->get()->row_array();
        if($check_)
            return true;
        return false;
    }




    public function download($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        $file_name = date('YmdHis').rand(1000,9999).'.jpg';
        $targetName = './uploadfiles/head/'.$file_name;
        $resource = fopen($targetName, 'a');
        fwrite($resource, $file);
        fclose($resource);
        return $file_name;
    }
}