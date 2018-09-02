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

    public function pg_list(){
        $per_page = 10;//每页显示多少调数据
        $data['keyword'] = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $this->db->select('count(1) num');
        $this->db->distinct('xiaoqu,wy_id');
        $this->db->from('pg_xiaoqu');
        if($data['keyword']){
            $this->db->like('xiaoqu', $data['keyword']);
        }
       // $this->db->group_by('xiaoqu');
       // $this->db->group_by('wy_id');
       // $sql_ = $this->db->get_compiled_select();
       // $total_sql = "select count(1) num from (" . "$sql_" . ") t";
        //$rs_total = $this->db->query($total_sql)->row();
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
        $this->db->select('a.xiaoqu,c.name wy_name,round(max(b_price)) min_price,round(max(b_price) * 1.15) as max_price',false);
        $this->db->from('pg_xiaoqu a');
        $this->db->join('pg_area b','a.area_id = b.id','left');
        $this->db->join('pg_wy c','a.wy_id = c.id','left');
        if($data['keyword']){
            $this->db->like('a.xiaoqu', $data['keyword']);
        }
        $this->db->group_by('a.xiaoqu');
        $this->db->group_by('a.wy_id');
        $this->db->group_by('c.name');
        $this->db->limit($per_page, ($pageNum - 1) * $per_page );
        $this->db->order_by('a.xiaoqu','desc');
        $data['res_list'] = $this->db->get()->result_array();

        return $data;
    }

    public function api_get_xiaoqu_list(){
        $data['keyword'] = trim($this->input->post('search_')) ? trim($this->input->post('search_')) : '';
        $this->db->select('a.xiaoqu',false);
        $this->db->from('pg_xiaoqu a');
        $this->db->join('pg_area b','a.area_id = b.id','left');
        $this->db->join('pg_wy c','a.wy_id = c.id','left');
        if($data['keyword']){
            $this->db->like('a.xiaoqu', $data['keyword']);
        }else{
            $this->db->where('a.id', -1);
        }
        $this->db->group_by('a.xiaoqu');
        $this->db->order_by('a.xiaoqu','desc');
        $this->db->limit(10, 0);
        $data = $this->db->get()->result_array();

        return $data;
    }

    public function get_zcs(){
        $data['keyword'] = trim($this->input->post('search_')) ? trim($this->input->post('search_')) : '';
        //$this->db->distinct('a.zcs');
        $this->db->select('a.zcs,round(max(b_price)) min_price,round(max(b_price) * 1.15) as max_price',false);
        $this->db->from('pg_xiaoqu a');
        $this->db->join('pg_area b','a.area_id = b.id','left');
        $this->db->join('pg_wy c','a.wy_id = c.id','left');
        $this->db->where('a.xiaoqu', $data['keyword']);
        $this->db->group_by('a.zcs');
        $this->db->order_by('a.zcs','asc');
        $data = $this->db->get()->result_array();
        return $data;
    }
}