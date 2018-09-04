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
            if($res->role_id == -2){
                $this->db->from('user');
                $this->db->where('id', $res->c_cust_id);
                $cust = $this->db->get()->row();
                if(!$cust){
                    return 2;
                }
                if($cust->flag == 2){
                    return 2;
                }
            }
            $role_p = $this->db->select()->where('id',$res->role_id)->from('role')->get()->row();
            $token = uniqid();
            $user_info['wx_token'] = $token;
            $user_info['wx_user_id'] = $res->id;
            $user_info['wx_username'] = $res->username;
            $user_info['wx_password'] = $res->password;
            $user_info['wx_role_id'] = $res->role_id;
            $user_info['wx_is_manager'] = $res->is_manager;
            if($res->role_id < 0){
                $user_info['wx_rel_name'] = $res->c_rel_name;
                $user_info['wx_role_name'] = "渠道客户";
                $user_info['wx_permission_id'] = 99;
            }else{
                $user_info['wx_rel_name'] = $res->rel_name;
                $user_info['wx_role_name'] = $role_p->name;
                $user_info['wx_permission_id'] = $role_p->permission_id;

            }
            $user_info['wx_company_id'] = $res->company_id;
            $user_info['wx_user_pic'] = $res->pic;
            $this->session->set_userdata($user_info);
            return 1;
        }
        return 0;
    }

    public function check_person(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        if($check_)
            return true;
        return false;
    }

    public function save_person($person_info){
        $openid = $this->session->userdata('openid');
        if($check_ = $this->check_person()){
            return -1;
        }
        $insert_data = array(
            'openid' => $openid,
            'subscribe' => $person_info['subscribe'],
            'nickname' => $person_info['nickname'],
            'sex' => $person_info['sex'],
            'language' => $person_info['language'],
            'city' => $person_info['city'],
            'province' => $person_info['province'],
            'country' => $person_info['country'],
            'head_img' => $this->download($person_info['headimgurl']),
            'subscribe_scene' => $person_info['subscribe_scene'],
            'cdate' => date('Y-m-d H:i:s',time())
        );
        $this->db->insert('fp_wx_user', $insert_data);
        return 1;
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