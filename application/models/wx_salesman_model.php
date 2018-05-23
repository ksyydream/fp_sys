<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
class Wx_salesman_model extends MY_Model
{
    private $role_id = 0;
    private $user_id = 0;
    private $is_manager = 0;
    private $company_id = 0;
    public function __construct()
    {
        parent::__construct();
        $this->user_id = $this->session->userdata('wx_user_id') ? $this->session->userdata('wx_user_id') : -1;
        $this->role_id = $this->session->userdata('wx_role_id') ? $this->session->userdata('wx_role_id') : -1;
        $this->is_manager = $this->session->userdata('wx_is_manager') ? $this->session->userdata('wx_is_manager') : -1;
        $this->company_id = $this->session->userdata('wx_company_id') ? $this->session->userdata('wx_company_id') : -1;
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

    public function save_customer() {
        $data = array(
            'c_company_name' => $this->input->post('c_company_name'),
            'c_rel_name' => $this->input->post('c_rel_name'),
            'c_path' => $this->input->post('c_path'),
            'c_tel' => $this->input->post('c_tel'),
            'c_mobile' => $this->input->post('c_mobile'),
            'c_lat' => $this->input->post('c_lat'),
            'c_lng' => $this->input->post('c_lng'),
            'parent_id' => $this->session->userdata('wx_user_id'),
            'c_b_lat' => $this->input->post('c_b_lat'),
            'c_b_lng' => $this->input->post('c_b_lng'),
            'company_id' => $this->session->userdata('wx_company_id'),
            'role_id' => -1,
            'flag' => 1
        );
        if(!$data['c_company_name'] || !$data['c_rel_name'] || !$data['c_path'] || !$data['c_tel'] || !$data['c_mobile'] || !$data['c_lat'] || !$data['c_lng']){
            return -2;
        }
        if($id = $this->input->post('id')){
            $check_ = $this->db->select()->from('user')->where(array('id' => $id, 'parent_id' => $this->session->userdata('wx_user_id'), 'flag' => 1))->get()->row();
            if($check_){
                unset($data['parent_id']);
                unset($data['company_id']);
                $this->db->where('id' ,$id)->update('user',$data);
            }else{
                return -2;
            }

        }else{

            $data['password'] = sha1('888888');
            $data['username'] = $this->get_username($this->session->userdata('wx_company_id'));
            if($data['username'] == -1){
                return -3;
            }
            $this->db->insert('user' ,$data);
        }
        return 1;
    }

    public function get_username($company_id){
        $company = $this->db->select()->from('company')->where('id', $company_id)->get()->row_array();
        if(!$company){
            return -1;
        }
        $this->db->select('count(1) num');
        $this->db->from('user');
        $this->db->where('id', $company_id);
        $this->db->where('role_id', -1);
        $rs_total = $this->db->get()->row();
        $total_rows = $rs_total->num;
        $total_rows = $total_rows + 115;
        $username_id = $this->check_super_id($total_rows);
        if($username_id == -1){
            return -1;
        }
        return $company['sx'] . sprintf('%04s', $username_id);
    }

    public function check_super_id($id){
        if($id > 9999){
            return -1;
        }
        $super_id = $this->db->select()->from('super_id')->where('super_uid',$id)->get()->row_array();
        if($super_id){
            $id++;
            return $this->check_super_id($id);
        }else{
            return $id;
        }
    }

    public function get_customer($id){
        $this->db->select('a.*,b.rel_name p_rel_name');
        $this->db->from('user a');
        $this->db->join('user b','a.parent_id = b.id','left');
        $this->db->where('a.parent_id', $this->session->userdata('wx_user_id'));
        $this->db->where('a.role_id', -1);
        $this->db->where('a.id', $id);
        return $this->db->get()->row_array();
    }

    public function customer_list(){
        $per_page = 10;//每页显示多少调数据
        $this->db->select('count(1) num');
        $this->db->from('user');
        /*
        $this->db->group_start();
        $this->db->where('', $this->session->userdata('b_id'));
        $this->db->or_where('create_id', $this->session->userdata('b_id'));
        $this->db->group_end();
        */
        $this->db->where('company_id', $this->company_id);
        if($this->is_manager == 1){
            $this->db->where('parent_id', $this->user_id);
        }
        $this->db->where('role_id', -1);
        $rs_total = $this->db->get()->row();
        //总记录数
        $total_rows = $rs_total->num;
        $total_page = ceil($total_rows/$per_page); //总页数
        $pageNum = $this->uri->segment(3) ? $this->uri->segment(3) : 1;//当前页

        if($pageNum > $total_page & $total_rows > 0 || $pageNum <1){
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'><script>alert('页码错误');history.back();</script>";
            exit();
        }
        $data['total_rows'] = $total_rows;
        $data['total_page'] = $total_page;
        $data['pageNum'] = $pageNum;

        //list
        $this->db->select('a.*,b.rel_name p_rel_name');
        $this->db->from('user a');
        $this->db->join('user b','a.parent_id = b.id','left');
        $this->db->where('company_id', $this->company_id);
        if($this->is_manager == 1){
            $this->db->where('parent_id', $this->user_id);
        }
        $this->db->where('a.role_id', -1);
        $this->db->limit($per_page, ($pageNum - 1) * $per_page );
        $this->db->order_by('a.id','desc');
        $data['res_list'] = $this->db->get()->result_array();
        return $data;
    }

    public function get_ywy_list(){
        $this->db->select();
        $this->db->from('user');
        $this->db->where('role_id', 7);
        $this->db->where('company_id', $this->company_id);
        $data = $this->db->get()->result_array();
        return $data;
    }
}