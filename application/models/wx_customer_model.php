<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/3
 * Time: 下午3:22
 */
class Wx_customer_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function save_ywy() {
        $data = array(
            'c_rel_name' => $this->input->post('c_rel_name'),
            'c_tel' => $this->input->post('c_tel'),
            'parent_id' => $this->parent_id,
            'company_id' => $this->company_id,
            'c_cust_id' => $this->user_id,
            'role_id' => -2,
            'flag' => 1
        );
        if(!$data['c_rel_name'] || !$data['c_tel']){
            return -2;
        }
        if($id = $this->input->post('id')){
            unset($data['parent_id']);
            unset($data['flag']);
            unset($data['company_id']);
            $this->db->where('id' ,$id)->update('user',$data);
        }else{

            $data['password'] = sha1('888888');
            $data['username'] = $this->get_username4cust($this->user_id);
            if($data['username'] == -1){
                return -3;
            }
            $this->db->insert('user' ,$data);
        }
        return 1;
    }

    public function get_username4cust($user_id){
        $cust = $this->get_user_info4wx($user_id);
        if(!$cust){
            return -1;
        }
        $this->db->select('count(1) num');
        $this->db->from('user');
        $this->db->where('c_cust_id', $user_id);
        $this->db->where('role_id', -2);
        $rs_total = $this->db->get()->row();
        $total_rows = $rs_total->num;
        $total_rows = $total_rows + 1;
        if($total_rows >= 999){
            return -1;
        }
        return $cust['username'] . sprintf('%03s', $total_rows);
    }

    public function list_ywy(){
        $per_page = 10;//每页显示多少调数据
        $this->db->select('count(1) num');
        $this->db->from('user');
        $this->db->where('company_id', $this->company_id);
        $this->db->where('role_id', -2);
        $this->db->where('c_cust_id', $this->user_id);
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
        $this->db->select('*');
        $this->db->from('user');
        $this->db->where('company_id', $this->company_id);
        $this->db->where('role_id', -2);
        $this->db->where('c_cust_id', $this->user_id);
        $this->db->limit($per_page, ($pageNum - 1) * $per_page );
        $this->db->order_by('id','desc');
        $data['res_list'] = $this->db->get()->result_array();
        return $data;
    }

    public function change_user_flag($id, $flag){
        $this->db->where(array(
            'company_id' => $this->company_id,
            'c_cust_id' => $this->user_id,
            'id' => $id
        ));
        $res = $this->db->update('user', array('flag'=>$flag));
        return $res;
    }

}