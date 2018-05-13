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
       $row = $this->db->select()->from('user')->where('id',$id)->get()->row_array();
        if (!$row){
            return -1;
        }else{
            return $row['token'];
        }
    }

    public function check_openid(){
        $openid = $this->session->userdata('openid');

        $this->db->select('a.id,a.rel_name,b.name,b.sum,c.name role_name')->from('user a');
        $this->db->join('company b','a.company_id = b.id','left');
        $this->db->join('role c','c.id = a.role_id','left');
        $this->db->where('a.openid',$openid);
        $row=$this->db->get()->row_array();
        if($row){
            $res = $this->set_session_wx($row['id']);
            if($res==1)
                return 1;
        }
        $this->session->unset_userdata('wx_user_id');
        $this->session->unset_userdata('wx_role_id');
        return -1;
    }

    public function set_session_wx($id){
        $this->db->from('user');
        $this->db->where('id', $id);
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $res = $rs->row();
            if($res->flag==2){
                return 2;
            }
            $role_p = $this->db->select()->where('id',$res->role_id)->from('role')->get()->row();
            $company_flag = $this->db->where('id',$res->company_id)->from('company')->get()->row_array();
            if($role_p->permission_id !=1){
                if($company_flag){
                    if($company_flag['flag']==2 && $role_p->permission_id !=1){
                        return 3;
                    }
                }else{
                    return 3;
                }
            }
            $token = uniqid();
            $user_info['wx_token'] = $token;
            $user_info['wx_user_id'] = $res->id;
            $user_info['wx_username'] = $res->username;
            $user_info['wx_password'] = $res->password;
            $user_info['wx_rel_name'] = $res->rel_name;
            $user_info['wx_role_id'] = $res->role_id;
            $user_info['wx_role_name'] = $role_p->name;
            $user_info['wx_permission_id'] = $role_p->permission_id;
            $user_info['wx_company_id'] = $res->company_id;
            $user_info['wx_user_pic'] = $res->pic;
            $this->session->set_userdata($user_info);
            return 1;
        }
        return 0;
    }
}