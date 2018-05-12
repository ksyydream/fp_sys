<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * 网站后台模型
 *
 * @package		app
 * @subpackage	core
 * @category	model
 * @author		yaobin<645894453@qq.com>
 *        
 */
class Manage_model extends MY_Model
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function __destruct ()
    {
        parent::__destruct();
    }
    
    /**
     * 用户登录检查
     * 
     * @return boolean
     */
    public function check_login ()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $this->db->select('a.*,b.permission_id');
        $this->db->from('user a');
        $this->db->join('role b','a.role_id = b.id','inner');
        $this->db->where('a.username', $username);
        $this->db->where('a.password', sha1($password));
        //$this->db->where('b.permission_id <= 4');
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
        	$res = $rs->row();
        	$user_info['user_id'] = $res->id;
            $user_info['username'] = $username;
            $user_info['rel_name'] = $res->rel_name;
          //  $user_info['role_id'] = $res->role_id;
            $user_info['permission_id'] = $res->permission_id;
            $user_info['company_id'] = $res->company_id;
            $subids = $this->db->select()->from('user_subsidiary')->where('user_id',$res->id)->get()->result_array();
            $sids = array();
            if($subids){
                foreach($subids as $id){
                    $sids[]=$id['subsidiary_id'];
                }
            }
            $user_info['subsidiary_id_array'] = $sids;

            $pids = $this->db->select()->from('user_position')->where('user_id',$res->id)->get()->result_array();
            $ids = array();
            if($pids){
                foreach($pids as $id){
                    $ids[]=$id['pid'];
                }
            }
            $user_info['position_id_array'] = $ids;
            $this->session->set_userdata($user_info);
            return true;
        }
        return false;
    }
    
    /**
     * 修改密码
     * 
     */
    public function change_pwd ()
    {
        $username = $this->input->post('username');
        $newpassword = $this->input->post('newpassword');
        
		$rs=$this->db->where('username', $username)->update('user', array('password'=>sha1($newpassword)));
        if ($rs) {
            return 1;
        } else {
            return $rs;
        }
    }
    /**
     * 确认是否是下属关系
     */
    public function Is_subordinate($id){
        $user_row = $this->db->select('b.permission_id,a.company_id')->from('user a')
            ->join('role b','a.role_id = b.id','left')
            ->where('a.id',$id)->get()->row_array();
        $user_sub = $this->db->select('b.*')->from('user a')
            ->join('user_subsidiary b','a.id = b.user_id','left')
            ->where('a.id',$id)->get()->result_array();
        if($this->session->userdata('company_id') != $user_row['company_id']){
            return -1;
        }
        if($this->session->userdata('permission_id') >= $user_row['permission_id']){
            return -1;
        }
        if($this->session->userdata('permission_id') == 2){
            return 1;
        }
        foreach($this->session->userdata('subsidiary_id_array') as $item){
            foreach($user_sub as $sub1){
                if($item == $sub1['subsidiary_id']){
                    return 1;
                }
            }
        }
        return -1;
    }
    /**
     * 公司信息
     */
    public function list_company(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('company a');
        $this->db->join('power_menu b','b.id = a.menu_id','left');
        if($this->session->userdata('permission_id') > 1) {
            $this->db->where('a.id', $this->session->userdata('company_id'));
        }
        if($this->input->post('company'))
            $this->db->like('a.name',trim($this->input->post('company')));
        if($this->input->post('flag'))
            $this->db->where('a.flag',$this->input->post('flag'));
        if($this->input->post('power_id'))
            $this->db->where('b.id',$this->input->post('power_id'));
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;
        $data['company'] = $this->input->post('company')?trim($this->input->post('company')):null;
        $data['flag'] = $this->input->post('flag')?$this->input->post('flag'):null;
        $data['menuid'] = $this->input->post('power_id')?$this->input->post('power_id'):null;
        //list
        $this->db->select('a.*,b.menu_name')->from('company a');
        $this->db->join('power_menu b','b.id = a.menu_id','left');
        if($this->session->userdata('permission_id') > 1) {
            $this->db->where('a.id', $this->session->userdata('company_id'));
        }
        if($this->input->post('company'))
            $this->db->like('a.name',trim($this->input->post('company')));
        if($this->input->post('flag'))
            $this->db->where('a.flag',$this->input->post('flag'));
        if($this->input->post('power_id'))
            $this->db->where('b.id',$this->input->post('power_id'));
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        $data['menu_list'] = $this->db->select()->from('power_menu')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function save_company() {
        $data = array(
            'name' => $this->input->post('name'),
            'address' => $this->input->post('address'),
            'tel' => $this->input->post('tel'),
            'sx' =>strtoupper($this->input->post('sx')),
            'flag'=> $this->input->post('flag')? 1 : 2
        );
        if(!$data['sx']){
            return -1;
        }
        $this->db->trans_start();//--------开始事务

        if($this->input->post('id')){//修改
            $check_ = $this->db->select('*')->from('company')->where('sx', $data['sx'])->where('id <>', $this->input->post('id'))->get()->row_array();
            if($check_){
                return -2;
            }
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('company', $data);
        } else {
            $check_ = $this->db->select('*')->from('company')->where('sx', $data['sx'])->get()->row_array();
            if($check_){
                return -2;
            }
            $this->db->insert('company', $data);
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_company($id) {
        $this->db->select('a.*,b.menu_name');
        $this->db->from('company a');
        $this->db->join('power_menu b','a.menu_id = b.id','left');
        return $this->db->where('a.id', $id)->get()->row_array();
    }

    public function delete_company($id) {
        $this->db->where('id', $id);
        return $this->db->delete('company');
    }

    public function get_menu_list(){
        return $this->db->select()->from('power_menu')->get()->result();
    }
    /**
     * 分店信息
     */
    public function list_subsidiary(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('subsidiary');
        if($this->session->userdata('permission_id') == 2) {
            $this->db->where('company_id', $this->session->userdata('company_id'));
        } else if($this->session->userdata('permission_id') > 2) {
            $this->db->where_in('id', $this->session->userdata('subsidiary_id_array'));
        }

        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;
        $data['company_id'] = null;

        //list
        $this->db->select('a.*, b.name AS company_name');
        $this->db->from('subsidiary a');
        $this->db->join('company b', 'a.company_id = b.id', 'left');
        if($this->session->userdata('permission_id') == 2) {
            $this->db->where('a.company_id', $this->session->userdata('company_id'));
        } else if($this->session->userdata('permission_id') > 2) {
            $this->db->where_in('a.id', $this->session->userdata('subsidiary_id_array'));
        }

        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function save_subsidiary() {

        $data = array(
            'company_id' => $this->input->post('company_id'),
            'name' => $this->input->post('name')
        );

        $this->db->trans_start();//--------开始事务

        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('subsidiary', $data);
        } else {
            $this->db->insert('subsidiary', $data);
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_subsidiary($id) {
        return $this->db->get_where('subsidiary', array('id' => $id))->row_array();
    }

    public function delete_subsidiary($id) {
        if($this->session->userdata('permission_id') >2){
            return false;
        }
        $this->db->where('id', $id);
        if($this->session->userdata('permission_id') ==2){
            $this->db->where('company_id',$this->session->userdata('company_id'));
        }
        return $this->db->delete('subsidiary');
    }

    public function get_company_list() {
        if($this->session->userdata('permission_id') == 1) {
            return $this->db->get('company')->result();
        } else {
            return $this->db->get_where('company', array('id' => $this->session->userdata('company_id')))->result();
        }
    }

    public function get_company_list_age(){
        return $this->db->get('company')->result();
    }

    public function get_subsidiary_list_age($id){
        return $this->db->get_where('subsidiary', array('company_id' => $id))->result_array();
    }

    public function get_user_list_by_subsidiary_age($id){
        $this->db->select('a.*');
        $this->db->from('user a');
        $this->db->join('user_subsidiary b','a.id = b.user_id','left');
        $this->db->where('b.subsidiary_id',$id);
        return $this->db->get()->result_array();
    }

    public function get_subsidiary_list_by_company($id) {
        if($this->session->userdata('permission_id') <=2) {
            return $this->db->get_where('subsidiary', array('company_id' => $id))->result_array();
        } else {
            return $this->db->where_in('id', $this->session->userdata('subsidiary_id_array'))->from('subsidiary')->get()->result_array();
        }
    }

    /**
     * 角色信息
     */
    public function list_role(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('role');

        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*')->from('role');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function save_role() {
        $data = array(
            'name' => $this->input->post('name')
        );
        $this->db->trans_start();//--------开始事务

        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('role', $data);
        } else {
            $this->db->insert('role', $data);
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_role($id) {
        return $this->db->get_where('role', array('id' => $id))->row_array();
    }

    public function delete_role($id) {
        $this->db->where('id', $id);
        return $this->db->delete('role');
    }

    /**
     * 经纪人管理
     */
    public function list_user(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $mysql = "
              SELECT DISTINCT  a.id from user a
               LEFT JOIN user_position b on a.id = b.user_id
               LEFT JOIN user_subsidiary d on d.user_id = a.id
               LEFT JOIN role e on e.id = a.role_id
              where  a.id > 0
               ";
        if($this->session->userdata('permission_id')==1){
            $mysql.=" and e.permission_id >= ".$this->session->userdata('permission_id');
        }else{
            $mysql.=" and e.permission_id > ".$this->session->userdata('permission_id');
        }
        if($this->session->userdata('permission_id') >= 2) {
            $mysql.=" and a.company_id = ".$this->session->userdata('company_id');
        }
        if($this->input->post('rel_name'))
            $mysql .= " AND a.rel_name like '%".$this->input->post('rel_name')."%'";
        if($this->input->post('tel'))
            $mysql .= " AND a.tel like '%".$this->input->post('tel')."%'";
        if($this->input->post('flag'))
            $mysql .= " AND a.flag = '".$this->input->post('flag')."'";
        if($this->input->post('is_manager'))
            $mysql .= " AND a.is_manager = '".$this->input->post('is_manager')."'";
        if($this->input->post('position_id'))
            $mysql .= " AND b.pid = '".$this->input->post('position_id')."'";
        if($this->input->post('role_id'))
            $mysql .= " AND a.role_id = '".$this->input->post('role_id')."'";
        if($this->input->post('company_id'))
            $mysql .= " AND a.company_id = '".$this->input->post('company_id')."'";

        $mainsql = "select count(1) as num from (".$mysql.") a";
        $rs_total = $this->db->query($mainsql)->row();
        /* $this->db->select('count(1) as num');
         $this->db->from('user a');
         $this->db->join('user_position b','a.id = b.user_id','left');
         $this->db->join('user_subsidiary d','d.user_id = a.id','left');
         if($this->session->userdata('permission_id') == 2) {
             $this->db->where('a.company_id', $this->session->userdata('company_id'));

         } else if($this->session->userdata('permission_id') > 2) {
             $this->db->where_in('d.subsidiary_id', $this->session->userdata('subsidiary_id_array'));
         }
         if($this->input->post('rel_name'))
             $this->db->like('a.rel_name',$this->input->post('rel_name'));
         if($this->input->post('tel'))
             $this->db->like('a.tel',$this->input->post('tel'));
         if($this->input->post('flag'))
             $this->db->where('a.flag',$this->input->post('flag'));
         if($this->input->post('position_id'))
             $this->db->where('b.pid',$this->input->post('position_id'));
         if($this->input->post('role_id'))
             $this->db->where('a.role_id',$this->input->post('role_id'));
         if($this->input->post('company_id'))
             $this->db->where('a.company_id',$this->input->post('company_id'));
         if($this->input->post('subsidiary_id'))
             $this->db->where_in('d.subsidiary_id',$this->input->post('subsidiary_id'));
         //$this->db->group_by('a.id');
         $rs_total = $this->db->get()->row();*/
        //die(var_dump($this->db->last_query()));
        //总记录数
        $data['relname'] = $this->input->post('rel_name')?$this->input->post('rel_name'):null;
        $data['tel'] = $this->input->post('tel')?$this->input->post('tel'):null;
        $data['flag'] = $this->input->post('flag')?$this->input->post('flag'):null;
        $data['is_manager'] = $this->input->post('is_manager')?$this->input->post('is_manager'):null;
        $data['positionid'] = $this->input->post('position_id')?$this->input->post('position_id'):null;
        $data['roleid'] = $this->input->post('role_id')?$this->input->post('role_id'):null;
        $data['companyid'] = $this->input->post('company_id')?$this->input->post('company_id'):null;
        $data['countPage'] = $rs_total->num?$rs_total->num:0;

        $data['rel_name'] = null;
        //list
        $this->db->select('a.id,a.flag,a.rel_name,a.tel, b.name AS company_name, d.name AS role_name,d.permission_id');
        //$this->db->distinct('a.id');
        $this->db->from('user a');
        $this->db->join('company b', 'a.company_id = b.id', 'left');
        $this->db->join('role d', 'a.role_id = d.id', 'left');
        $this->db->join('user_position e', 'a.id = e.user_id', 'left');
        if($this->session->userdata('permission_id') >= 2) {
            $this->db->where('a.company_id', $this->session->userdata('company_id'));
        }
        if($this->input->post('rel_name'))
            $this->db->like('a.rel_name',$this->input->post('rel_name'));
        if($this->input->post('tel'))
            $this->db->like('a.tel',$this->input->post('tel'));
        if($this->input->post('flag'))
            $this->db->where('a.flag',$this->input->post('flag'));
        if($this->input->post('is_manager'))
            $this->db->where('a.is_manager',$this->input->post('is_manager'));
        if($this->input->post('position_id'))
            $this->db->where('e.pid',$this->input->post('position_id'));
        if($this->input->post('role_id'))
            $this->db->where('a.role_id',$this->input->post('role_id'));
        if($this->input->post('company_id'))
            $this->db->where('a.company_id',$this->input->post('company_id'));
        if($this->session->userdata('permission_id')==1){
            $this->db->where('d.permission_id >=',$this->session->userdata('permission_id'));
        }else{
            $this->db->where('d.permission_id >',$this->session->userdata('permission_id'));
        }

        $this->db->group_by('a.id,a.rel_name,a.tel, b.name, d.name,d.permission_id,a.flag');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        //die(var_dump($this->db->last_query()));
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function password_reset($id){
        $res1 = $this->Is_subordinate($id);
        if($res1==1 || $this->session->userdata('permission_id')==1){
            $res = $this->db->where('id',$id)->update('user',array('password'=>sha1('888888')));
            if($res){
                return 1;
            }else{
                return 2;
            }
        }else{
            return 2;
        }


    }

    public function save_user($pic = NULL) {
        $data = array(
            'username' => trim($this->input->post('tel')),
            'tel' => trim($this->input->post('tel')),
            'company_id' => $this->input->post('company_id'),
            'rel_name' => $this->input->post('rel_name'),
            'role_id' => $this->input->post('role_id'),
            'is_manager' => $this->input->post('is_manager'),
            'flag'=>$this->input->post('flag')
        );
        if(!empty($pic)) {
            $data['pic'] = $pic;
        }
        $this->db->trans_start();//--------开始事务

        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('user', $data);
            $user_id = $this->input->post('id');

        } else {
            $data['password']=sha1('888888');
            $this->db->insert('user', $data);
            $user_id = $this->db->insert_id();
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_user($id) {
        return $this->db->get_where('user', array('id' => $id))->row_array();
    }

    public function get_user_pid($id) {
        return $this->db->get_where('user_position', array('user_id' => $id))->result_array();
    }

    public function get_user_subid($id) {
        return $this->db->get_where('user_subsidiary', array('user_id' => $id))->result_array();
    }

    public function delete_user($id) {
        $res = $this->Is_subordinate($id);
        if($res == 1 || $this->session->userdata('permission_id')==1){
            $this->db->where('id', $id);
            return $this->db->delete('user');
        }else{
            return false;
        }

    }

    public function get_user_by_tel($tel,$id=null) {
        $data['tel']=$tel;
        if($id){
            $data['id <>'] = $id;
        }
        return $this->db->get_where('user', $data)->row_array();
    }

    public function get_role_list() {
        return $this->db->order_by('permission_id','asc')->order_by('id','asc')->get_where('role', array('id >' => 1,'permission_id >'=>$this->session->userdata('permission_id')))
            ->result_array();
    }

    public function get_position_list() {
        return $this->db->get_where('position', array('id >=' => 1))->result_array();
    }
    /**
     * 获取职务列表
     */
    public function list_position(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('position');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('position');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    /**
     * 保存职务
     */
    public function save_position(){
        $this->db->trans_start();
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('position', $this->input->post());
        }else{//新增
            $data = $this->input->post();
            $this->db->insert('position', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 删除职务
     */
    public function delete_position($id){
        $rs = $this->db->delete('position', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    /**
     * 获取职务详情
     */
    public function get_position($id){
        $this->db->select('*')->from('position')->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }

    /**
     * 获取代办进程列表
     */
    public function list_course(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('course');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('course');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    /**
     * 保存代办进程
     */
    public function save_course(){
        $this->db->trans_start();
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('course', $this->input->post());
        }else{//新增
            $data = $this->input->post();
            $this->db->insert('course', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 删除代办进程
     */
    public function delete_course($id){
        $rs = $this->db->delete('course', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    /**
     * 获取代办进程详情
     */
    public function get_course($id){
        $this->db->select('*')->from('course')->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }

    /**
     * 获取区镇列表
     */
    public function list_towns(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('towns');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('towns');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    /**
     * 保存区镇
     */
    public function save_towns(){
        $this->db->trans_start();
        $data = array(
            'id'=>$this->input->post('id'),
            'towns_name'=>$this->input->post('towns_name'),
            'flag'=>$this->input->post('flag')?1:2
        );
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('towns', $data);
        }else{//新增
            unset($data['id']);
            $this->db->insert('towns', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 删除区镇
     */
    public function delete_towns($id){
        $rs = $this->db->delete('towns', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    /**
     * 获取区镇详情
     */
    public function get_towns($id){
        $this->db->select('*')->from('towns')->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }

    /**
     * 套餐处理
     */
    public function list_menu(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('power_menu');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('power_menu');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function save_menu(){
        $this->db->trans_start();
        $data = array(
            'id'=>$this->input->post('id'),
            'menu_name'=>$this->input->post('menu_name'),
            'flag'=>$this->input->post('flag')?1:2
        );
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('power_menu', $data);
            $m_id = $this->input->post('id');
        }else{//新增
            unset($data['id']);
            $this->db->insert('power_menu', $data);
            $m_id = $this->db->insert_id();
        }
        $this->db->where('m_id',$m_id)->delete('power_menu_detail');
        if($this->input->post('p_id')){
            $subid=$this->input->post('p_id');
            foreach($subid as $id){
                $this->db->insert('power_menu_detail', array(
                    'p_id'=>$id,
                    'm_id'=>$m_id
                ));
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    public function get_menu($id){
       return $this->db->select()->from('power_menu')->where('id',$id)->get()->row_array();
    }
    /**
     * 获取小区列表
     */
    public function list_xiaoqu(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('xiaoqu');
        if($this->input->post('flag'))
            $this->db->where("flag",$this->input->post('flag'));
        if($this->input->post('towns_id'))
            $this->db->where("towns_id",$this->input->post('towns_id'));
        if($this->input->post('name'))
            $this->db->like("name",trim($this->input->post('name')));
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;
        $data['flag'] = $this->input->post('flag')?trim($this->input->post('flag')):null;
        $data['name'] = $this->input->post('name')?trim($this->input->post('name')):null;
        $data['towns_id'] = $this->input->post('towns_id') ? trim($this->input->post('towns_id')):null;
        //list
        $this->db->select('a.*,b.towns_name');
        $this->db->from('xiaoqu a');
        $this->db->join('towns b','a.towns_id = b.id','left');
        if($this->input->post('flag'))
            $this->db->where("a.flag",$this->input->post('flag'));
        if($this->input->post('towns_id'))
            $this->db->where("a.towns_id",$this->input->post('towns_id'));
        if($this->input->post('name'))
            $this->db->like("a.name",trim($this->input->post('name')));
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['towns_list'] = $this->db->from('towns')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function get_towns_list(){
        return $this->db->select()->where('flag',1)->from('towns')->get()->result();
    }
    /**
     * 保存小区
     */
    public function save_xiaoqu(){
        $this->db->trans_start();
        $data = array(
            'id'=>$this->input->post('id'),
            'name'=>$this->input->post('name'),
            'path'=>$this->input->post('path'),
            'towns_id'=>$this->input->post('towns_id'),
            'flag'=>$this->input->post('flag')?1:2
        );
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('xiaoqu', $data);
        }else{//新增
            unset($data['id']);
            $this->db->insert('xiaoqu', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 删除小区
     */
    public function delete_xiaoqu($id){
        $rs = $this->db->delete('xiaoqu', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    /**
     * 获取小区详情
     */
    public function get_xiaoqu($id){
        $this->db->select('*')->from('xiaoqu')->where('id', $id);
        $data = $this->db->get()->row_array();
        return $data;
    }

    /**
     * 获取资料类别列表
     */
    public function list_forum_type(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('forum_type');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('forum_type');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    /**
     * 保存资料类别
     */
    public function save_forum_type(){
        $this->db->trans_start();
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('forum_type', $this->input->post());
        }else{//新增
            $data = $this->input->post();
            $this->db->insert('forum_type', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 删除资料类别
     */
    public function delete_forum_type($id){
        $rs = $this->db->delete('forum_type', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    /**
     * 获取资料类别详情
     */
    public function get_forum_type($id){
        $this->db->select('*')->from('forum_type')->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }

    public function list_ticket(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('ticket');

        if($this->input->post('title'))
            $this->db->like('title',$this->input->post('title'));
        if($this->input->post('type'))
            $this->db->where('type',$this->input->post('type'));

        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        $data['title'] = $this->input->post('title')?$this->input->post('title'):null;
        $data['type'] = $this->input->post('type')?$this->input->post('type'):null;
        //list
        $this->db->select("a.*,b.name type_name");
        $this->db->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','inner');
        if($this->input->post('title'))
            $this->db->like('a.title',$this->input->post('title'));
        if($this->input->post('type'))
            $this->db->where('a.type',$this->input->post('type'));

        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        $data['type_list'] = $this->db->from('forum_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function delete_ticket($id){
        $rs = $this->db->delete('ticket', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    public function get_ticket($id){
        $this->db->select('a.*,b.name type_name,c.rel_name user_name')->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','left');
        $this->db->join('user c','c.id = a.user_id','left');
        $this->db->where('a.id',$id);
        $data['head'] = $this->db->get()->row();
        $data['id'] = $id;
        //die(var_dump($data));
        return $data;
    }

    public function download($id){

        $this->load->helper('download');
        $this->load->helper('file');
        $data=$this->db->select()->from('ticket')->where('id',$id)->get()->row_array();
        if ($data){
            $string = read_file('./uploadfiles/doc/'.$data['file']);
            //   $file_name='./uploadfiles/'.$data['url'];//需要下载的文件
            force_download($data['oldfile'],$string);
        }
    }

    public function list_news()
    {
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('news');

        if($this->input->post('title'))
            $this->db->like('title',$this->input->post('title'));

        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        $data['title'] = $this->input->post('title')?$this->input->post('title'):null;
        //list
        $this->db->select();
        $this->db->from('news');
        if($this->input->post('title'))
            $this->db->like('title',$this->input->post('title'));

        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function delete_news($id){
        $rs = $this->db->delete('news', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    public function get_news($id){
        $this->db->select()->from('news');
        $this->db->where('id',$id);
        $data['head'] = $this->db->get()->row();
        $data['id'] = $id;
        //die(var_dump($data));
        return $data;
    }

    public function list_questions()
    {
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('question');

        if($this->input->post('title'))
            $this->db->like('title',$this->input->post('title'));
        if($this->input->post('style'))
            $this->db->where('style',$this->input->post('style'));
        if($this->input->post('type'))
            $this->db->where('type_id',$this->input->post('type'));

        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        $data['title'] = $this->input->post('title')?$this->input->post('title'):null;
        $data['style'] = $this->input->post('style')?$this->input->post('style'):null;
        $data['type'] = $this->input->post('type')?$this->input->post('type'):null;
        //list
        $this->db->select('a.*,b.name');
        $this->db->from('question a');
        $this->db->join('question_type b','a.type_id = b.id','left');
        if($this->input->post('a.title'))
            $this->db->like('a.title',$this->input->post('title'));
        if($this->input->post('style'))
            $this->db->where('a.style',$this->input->post('style'));
        if($this->input->post('type'))
            $this->db->where('a.type_id',$this->input->post('type'));
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function delete_questions($id){
        $rs = $this->db->where('id',$id)->update('question', array('flag' => 2));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    public function use_questions($id){
        $rs = $this->db->where('id',$id)->update('question', array('flag' => 1));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    public function get_questions($id){
        $this->db->select('a.*,b.name')->from('question a');
        $this->db->join('question_type b','a.type_id = b.id','left');
        $this->db->where('a.id',$id);
        $data['head'] = $this->db->get()->row();
        $data['id'] = $id;
        //die(var_dump($data));
        return $data;
    }

    public function list_sum_log()
    {
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('sum_log a');
        $this->db->join('company b','a.company_id = b.id','left');
        $this->db->join('user c','a.user_id = c.id','left');

        if($this->input->post('company'))
            $this->db->like('b.name',trim($this->input->post('company')));
        if($this->input->post('user'))
            $this->db->like('c.rel_name',trim($this->input->post('user')));
        if($this->input->post('demo'))
            $this->db->like('a.demo',trim($this->input->post('demo')));
        if($this->input->post('style'))
            $this->db->where('a.style',$this->input->post('style'));
        if($this->input->POST('start_date')) {
            $this->db->where('a.created >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('a.created <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $rs_total = $this->db->get()->row();
        //总记录数

        $data['countPage'] = $rs_total->num;

        $data['company'] = $this->input->post('company')?trim($this->input->post('company')):null;
        $data['style'] = $this->input->post('style')?$this->input->post('style'):null;
        $data['user'] = $this->input->post('user') ? trim($this->input->post('user')):null;
        $data['demo'] = $this->input->post('demo') ? trim($this->input->post('demo')):null;
        $data['start_date'] = $this->input->post('start_date') ? trim($this->input->post('start_date')):null;
        $data['end_date'] = $this->input->post('end_date') ? trim($this->input->post('end_date')):null;
        //list
        $this->db->select('a.*,b.name,c.rel_name');
        $this->db->from('sum_log a');
        $this->db->join('company b','a.company_id = b.id','left');
        $this->db->join('user c','a.user_id = c.id','left');

        if($this->input->post('company'))
            $this->db->like('b.name',trim($this->input->post('company')));
        if($this->input->post('user'))
            $this->db->like('c.rel_name',trim($this->input->post('user')));
        if($this->input->post('demo'))
            $this->db->like('a.demo',trim($this->input->post('demo')));
        if($this->input->post('style'))
            $this->db->where('a.style',$this->input->post('style'));
        if($this->input->POST('start_date')) {
            $this->db->where('a.created >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('a.created <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
       // $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function delete_sum_log($id){
        $rs = $this->db->delete('sum_log', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    public function list_agenda(){
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(distinct(a.id)) as num',false);
        $this->db->from('agenda a');
        $this->db->join('user b','a.user_id = b.id','inner');
        $this->db->join('role c','c.id = b.role_id','inner');
        $this->db->join('user_subsidiary d','d.user_id = b.id','inner');
        if($this->input->post('user_id')){
            $this->db->where('a.user_id',$this->input->post('user_id'));
        }
        if($this->input->post('status')){
            $this->db->where('a.status',$this->input->post('status'));
        }
        if($this->input->post('course')){
            $this->db->where('a.course',$this->input->post('course'));
        }
        if($this->input->post('num')){
            $this->db->like('a.num',trim($this->input->post('num')));
        }
        if($this->input->post('xq_name')){
            $this->db->like('a.xq_name',trim($this->input->post('xq_name')));
        }
        if($this->input->post('dbgh_id')){
            $this->db->where('a.dbgh_id',$this->input->post('dbgh_id'));
        }
        if($this->input->post('dbyh_id')){
            $this->db->where('a.dbyh_id',$this->input->post('dbyh_id'));
        }
        if($this->input->POST('company_id')) {
            $this->db->where('b.company_id', $this->input->POST('company_id'));
        }
        if($this->input->POST('subsidiary_id')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary_id'));
        }
        if($this->input->POST('user')) {
            $this->db->where('b.id', $this->input->POST('user'));
        }
        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        if(!empty($company_id)) {
            $this->db->where('b.company_id', $company_id);
        }
        if($this->input->POST('Cstart_date')) {
            $this->db->where('a.cdate >=', $this->input->POST('Cstart_date'));
        }
        if($this->input->POST('Cend_date')) {
            $this->db->where('a.cdate <=', $this->input->POST('Cend_date'));
        }
        if($this->input->POST('Estart_date')) {
            $this->db->where('a.edate >=', $this->input->POST('Estart_date'));
        }
        if($this->input->POST('Eend_date')) {
            $this->db->where('a.edate <=', $this->input->POST('Eend_date'));
        }

        $rs_total = $this->db->get()->row();
        //总记录数

        $data['countPage'] = $rs_total->num;

        $data['company_id'] = $this->input->post('company_id')?$this->input->post('company_id'):null;
        $data['subsidiary_id'] = $this->input->post('subsidiary_id')?$this->input->post('subsidiary_id'):null;
        $data['user_id'] = $this->input->post('user_id')?$this->input->post('user_id'):null;
        $data['dbgh_id'] = $this->input->post('dbgh_id')?$this->input->post('dbgh_id'):null;
        $data['dbyh_id'] = $this->input->post('dbyh_id')?$this->input->post('dbyh_id'):null;
        $data['course'] = $this->input->post('course')?$this->input->post('course'):null;
        $data['status'] = $this->input->post('status')?$this->input->post('status'):null;
        $data['num'] = $this->input->post('num') ? trim($this->input->post('num')):null;
        $data['xq_name'] = $this->input->post('xq_name') ? trim($this->input->post('xq_name')):null;
        $data['Cstart_date'] = $this->input->post('Cstart_date') ? $this->input->post('Cstart_date') :"";
        $data['Cend_date'] = $this->input->post('Cend_date') ? $this->input->post('Cend_date') :"";
        $data['Estart_date'] = $this->input->post('Estart_date') ? $this->input->post('Estart_date') :"";
        $data['Eend_date'] = $this->input->post('Eend_date') ? $this->input->post('Eend_date') :"";
        //list
        $this->db->select('a.*,b.rel_name,f.name course_name,u1.rel_name gh_name,u1.tel gh_tel,u2.rel_name yh_name,u2.tel yh_tel');
        $this->db->distinct('a.id');
        $this->db->from('agenda a');
        $this->db->join('user b','a.user_id = b.id','inner');
        $this->db->join('user u1','a.dbgh_id = u1.id','left');
        $this->db->join('user u2','a.dbyh_id = u2.id','left');
        $this->db->join('role c','c.id = b.role_id','inner');
        $this->db->join('user_subsidiary d','d.user_id = b.id','inner');
        $this->db->join('course f','f.id = a.course','left');
        if($this->input->post('user_id')){
            $this->db->where('a.user_id',$this->input->post('user_id'));
        }
        if($this->input->post('status')){
            $this->db->where('a.status',$this->input->post('status'));
        }
        if($this->input->post('course')){
            $this->db->where('a.course',$this->input->post('course'));
        }
        if($this->input->post('num')){
            $this->db->like('a.num',trim($this->input->post('num')));
        }
        if($this->input->post('xq_name')){
            $this->db->like('a.xq_name',trim($this->input->post('xq_name')));
        }
        if($this->input->post('dbgh_id')){
            $this->db->where('a.dbgh_id',$this->input->post('dbgh_id'));
        }
        if($this->input->post('dbyh_id')){
            $this->db->where('a.dbyh_id',$this->input->post('dbyh_id'));
        }
        if($this->input->POST('company_id')) {
            $this->db->where('b.company_id', $this->input->POST('company_id'));
        }
        if($this->input->POST('subsidiary_id')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary_id'));
        }
        if($this->input->POST('user')) {
            $this->db->where('b.id', $this->input->POST('user'));
        }
        if($this->input->POST('Cstart_date')) {
            $this->db->where('a.cdate >=', $this->input->POST('Cstart_date'));
        }
        if($this->input->POST('Cend_date')) {
            $this->db->where('a.cdate <=', $this->input->POST('Cend_date'));
        }
        if($this->input->POST('Estart_date')) {
            $this->db->where('a.edate >=', $this->input->POST('Estart_date'));
        }
        if($this->input->POST('Eend_date')) {
            $this->db->where('a.edate <=', $this->input->POST('Eend_date'));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        // $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        $data['course_list'] =  $this->db->select('*')->from('course')->get()->result_array();
        return $data;
    }

    public function delete_agenda($id) {
       return $this->db->where('id',$id)->update('agenda',array('flag'=>2));
    }

    public function get_dbgh_list() {
        $this->db->select('a.id,a.rel_name');
        $this->db->from('user a');
        $this->db->join('user_position b','a.id = b.user_id','left');
        $this->db->where('b.pid',8);
        $this->db->order_by('a.id');
        return $this->db->get()->result_array();
    }

    public function get_dbyh_list() {
        $this->db->select('a.id,a.rel_name');
        $this->db->from('user a');
        $this->db->join('user_position b','a.id = b.user_id','left');
        $this->db->where('b.pid',9);
        $this->db->order_by('a.id');
        return $this->db->get()->result_array();
    }

    public function get_icon_list(){
        $this->db->select();
        $this->db->from('icon');
        return $this->db->get()->result_array();
    }

    public function get_menu_detail($id){
        $this->db->select();
        $this->db->from('power_menu_detail');
        $this->db->where('m_id',$id);
        return $this->db->get()->result_array();
    }

    public function list_dclc(){
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('dclc');
        if($this->input->post('mobile'))
            $this->db->like('mobile',trim($this->input->post('mobile')));
        if($this->input->post('username'))
            $this->db->like('username',trim($this->input->post('username')));
        if($this->input->post('demo'))
            $this->db->like('demo',trim($this->input->post('demo')));
        if($this->input->post('flag'))
            $this->db->where('flag',$this->input->post('flag'));
        if($this->input->POST('start_date')) {
            $this->db->where('cdate >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('cdate <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $rs_total = $this->db->get()->row();
        //总记录数

        $data['countPage'] = $rs_total->num;

        $data['mobile'] = $this->input->post('mobile')?trim($this->input->post('mobile')):null;
        $data['username'] = $this->input->post('username')?$this->input->post('username'):null;
        $data['flag'] = $this->input->post('flag') ? trim($this->input->post('flag')):null;
        $data['demo'] = $this->input->post('demo') ? trim($this->input->post('demo')):null;
        $data['start_date'] = $this->input->post('start_date') ? trim($this->input->post('start_date')):null;
        $data['end_date'] = $this->input->post('end_date') ? trim($this->input->post('end_date')):null;
        //list
        $this->db->select();
        $this->db->from('dclc');

        if($this->input->post('mobile'))
            $this->db->like('mobile',trim($this->input->post('mobile')));
        if($this->input->post('username'))
            $this->db->like('username',trim($this->input->post('username')));
        if($this->input->post('demo'))
            $this->db->like('demo',trim($this->input->post('demo')));
        if($this->input->post('flag'))
            $this->db->where('flag',$this->input->post('flag'));
        if($this->input->POST('start_date')) {
            $this->db->where('cdate >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('cdate <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        // $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function edit_dclc($id){
        $row = $this->db->select()->from('dclc')->where('id',$id)->get()->row_array();
        return $row;
    }

    public function save_dclc(){
        if(!$this->input->post('id')){
            return -1;
        }
        $data = array(
            'flag'=>$this->input->post('flag'),
            'mark'=>$this->input->post('mark')
        );
        $res = $this->db->where('id',$this->input->post('id'))->update('dclc',$data);
        if($res){
            return 1;
        }else{
            return -1;
        }

    }

    public function list_pg(){
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('fj_xiaoqu');
        if($this->input->post('xiaoqu'))
            $this->db->like('xiaoqu',trim($this->input->post('xiaoqu')));
        if($this->input->post('flag'))
            $this->db->where('flag',$this->input->post('flag'));
        $rs_total = $this->db->get()->row();
        //总记录数

        $data['countPage'] = $rs_total->num;

        $data['xiaoqu'] = $this->input->post('xiaoqu')?trim($this->input->post('xiaoqu')):null;
        $data['flag'] = $this->input->post('flag') ? trim($this->input->post('flag')):null;
        //list
        $this->db->select('a.*,b.name area_name');
        $this->db->from('fj_xiaoqu a');
        $this->db->join('fj_area b','a.area_id = b.id');
        if($this->input->post('xiaoqu'))
            $this->db->like('xiaoqu',trim($this->input->post('xiaoqu')));
        if($this->input->post('flag'))
            $this->db->where('flag',$this->input->post('flag'));
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        // $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function get_fj_area(){
        $area = $this->db->select()->from('fj_area')->get()->result();
        return $area;
    }

    public function get_fj_type(){
        $type = $this->db->select()->from('fj_xiaoqu_type')->get()->result();
        return $type;
    }

    public function save_pg(){

        //检测是否存在相同名字的小区

        if($xiaoqu_id = $this->input->post('id')){
            //检测是否存在相同名字的小区
            $row_ = $this->db->select()->from('fj_xiaoqu')->where(array(
                'xiaoqu'=>trim($this->input->post('xiaoqu')),
                'id <>'=>$xiaoqu_id
            ))->get()->row();
            if($row_){
                return -1;
            }
            //开始保存
            $xiaoqu_arr = array(
                'xiaoqu'=>trim($this->input->post('xiaoqu')),
                'flag'=>$this->input->post('flag'),
                'area_id'=>$this->input->post('area_id')
            );
            $this->db->where('id',$xiaoqu_id)->update('fj_xiaoqu',$xiaoqu_arr);
            $this->db->delete('fj_xiaoqu_detail',array('xiaoqu_id'=>$xiaoqu_id));
            $type_ids = $this->input->post('type_id');
            $pgjs=$this->input->post('pgj');
            if($type_ids){
                foreach($type_ids as $k=>$type_id){
                    $this->db->insert('fj_xiaoqu_detail',array(
                        'xiaoqu_id'=>$xiaoqu_id,
                        'type_id'=>$type_id,
                        'pgj'=>$pgjs[$k]
                    ));
                }
            }
        }else{
            //检测是否存在相同名字的小区
            $row_ = $this->db->select()->from('fj_xiaoqu')->where(array(
                'xiaoqu'=>trim($this->input->post('xiaoqu'))
            ))->get()->row();
            if($row_){
                return -1;
            }
            //开始新增
            $xiaoqu_arr = array(
                'xiaoqu'=>trim($this->input->post('xiaoqu')),
                'flag'=>$this->input->post('flag'),
                'area_id'=>$this->input->post('area_id')
            );
            $this->db->insert('fj_xiaoqu',$xiaoqu_arr);
            $insert_id = $this->db->insert_id();
            $type_ids = $this->input->post('type_id');
            $pgjs=$this->input->post('pgj');
            if($type_ids){
                foreach($type_ids as $k=>$type_id){
                    $this->db->insert('fj_xiaoqu_detail',array(
                        'xiaoqu_id'=>$insert_id,
                        'type_id'=>$type_id,
                        'pgj'=>$pgjs[$k]
                    ));
                }
            }
        }
        return 1;
    }

    public function get_pg($id){
        $data = $this->db->select()->from('fj_xiaoqu')->where('id',$id)->get()->row_array();
        $data['list'] = $this->db->select()->from('fj_xiaoqu_detail')->where('xiaoqu_id',$id)->get()->result();
        return $data;
    }

    public function list_pg_qq(){
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('fj_pg_qq');
        $rs_total = $this->db->get()->row();
        //总记录数

        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('fj_pg_qq');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        // $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    /**
     * 保存客服QQ
     */
    public function save_pg_qq(){
        $this->db->trans_start();
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('fj_pg_qq', $this->input->post());
        }else{//新增
            $data = $this->input->post();
            $this->db->insert('fj_pg_qq', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 删除客服QQ
     */
    public function delete_pg_qq($id){
        $rs = $this->db->delete('fj_pg_qq', array('id' => $id));
        if($rs){
            return 1;
        }else{
            return $this->db_error;
        }
    }

    /**
     * 获取客服QQ
     */
    public function get_pg_qq($id){
        $this->db->select('*')->from('fj_pg_qq')->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }

    public function list_pg_msg(){
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('fj_msg');
        if($this->input->post('mobile'))
            $this->db->like('mobile',trim($this->input->post('mobile')));
        if($this->input->post('username'))
            $this->db->like('username',trim($this->input->post('username')));
        if($this->input->post('demo'))
            $this->db->like('demo',trim($this->input->post('demo')));
        if($this->input->post('flag'))
            $this->db->where('flag',$this->input->post('flag'));
        if($this->input->POST('start_date')) {
            $this->db->where('cdate >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('cdate <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $rs_total = $this->db->get()->row();
        //总记录数

        $data['countPage'] = $rs_total->num;

        $data['mobile'] = $this->input->post('mobile')?trim($this->input->post('mobile')):null;
        $data['username'] = $this->input->post('username')?$this->input->post('username'):null;
        $data['flag'] = $this->input->post('flag') ? trim($this->input->post('flag')):null;
        $data['demo'] = $this->input->post('demo') ? trim($this->input->post('demo')):null;
        $data['start_date'] = $this->input->post('start_date') ? trim($this->input->post('start_date')):null;
        $data['end_date'] = $this->input->post('end_date') ? trim($this->input->post('end_date')):null;
        //list
        $this->db->select();
        $this->db->from('fj_msg');

        if($this->input->post('mobile'))
            $this->db->like('mobile',trim($this->input->post('mobile')));
        if($this->input->post('username'))
            $this->db->like('username',trim($this->input->post('username')));
        if($this->input->post('demo'))
            $this->db->like('demo',trim($this->input->post('demo')));
        if($this->input->post('flag'))
            $this->db->where('flag',$this->input->post('flag'));
        if($this->input->POST('start_date')) {
            $this->db->where('cdate >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('cdate <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        // $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function edit_pg_msg($id){
        $row = $this->db->select()->from('fj_msg')->where('id',$id)->get()->row_array();
        return $row;
    }

    public function save_pg_msg(){
        if(!$this->input->post('id')){
            return -1;
        }
        $data = array(
            'flag'=>$this->input->post('flag'),
            'mark'=>$this->input->post('mark')
        );
        $res = $this->db->where('id',$this->input->post('id'))->update('fj_msg',$data);
        if($res){
            return 1;
        }else{
            return -1;
        }

    }

    public function list_fin(){
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(distinct(a.id)) as num',false);
        $this->db->from('finance a');
        $this->db->join('user b','a.user_id = b.id','inner');
        $this->db->join('user c','a.create_user = c.id','inner');
        if($this->input->post('user_id')){
            $this->db->where('a.user_id',$this->input->post('user_id'));
        }
        if($this->input->post('status')){
            $this->db->where('a.status',$this->input->post('status'));
        }
        if($this->input->post('finance_num')){
            $this->db->like('a.finance_num',trim($this->input->post('finance_num')));
        }
        if($this->input->post('borrower_name')){
            $this->db->like('a.borrower_name',trim($this->input->post('borrower_name')));
        }
        if($this->input->POST('company_id')) {
            $this->db->where('a.company_id', $this->input->POST('company_id'));
        }
        if($this->input->POST('subsidiary_id')) {
            $this->db->where_in('a.subsidiary_id', $this->input->POST('subsidiary_id'));
        }
        if($this->input->POST('Cstart_date')) {
            $this->db->where('date_format(a.create_date, \'%Y-%m-%d\') >=', $this->input->POST('Cstart_date'));
        }
        if($this->input->POST('Cend_date')) {
            $this->db->where('date_format(a.create_date, \'%Y-%m-%d\') <=', $this->input->POST('Cend_date'));
        }
        if($this->input->POST('Tstart_date')) {
            $this->db->where('a.tijiao_date >=', $this->input->POST('Tstart_date'));
        }
        if($this->input->POST('Tend_date')) {
            $this->db->where('a.tijiao_date <=', $this->input->POST('Tend_date'));
        }
        if($this->input->POST('Estart_date')) {
            $this->db->where('a.end_date >=', $this->input->POST('Estart_date'));
        }
        if($this->input->POST('Eend_date')) {
            $this->db->where('a.end_date <=', $this->input->POST('Eend_date'));
        }
        //$this->db->where('a.flag',1);
        $rs_total = $this->db->get()->row();
        //总记录数

        $data['countPage'] = $rs_total->num;

        $data['company_id'] = $this->input->post('company_id')?$this->input->post('company_id'):null;
        $data['subsidiary_id'] = $this->input->post('subsidiary_id')?$this->input->post('subsidiary_id'):null;
        $data['user_id'] = $this->input->post('user_id')?$this->input->post('user_id'):null;
        $data['status'] = $this->input->post('status')?$this->input->post('status'):null;
        $data['finance_num'] = $this->input->post('finance_num') ? trim($this->input->post('finance_num')):null;
        $data['borrower_name'] = $this->input->post('borrower_name') ? trim($this->input->post('borrower_name')):null;
        $data['Cstart_date'] = $this->input->post('Cstart_date') ? $this->input->post('Cstart_date') :"";
        $data['Cend_date'] = $this->input->post('Cend_date') ? $this->input->post('Cend_date') :"";
        $data['Tstart_date'] = $this->input->post('Tstart_date') ? $this->input->post('Tstart_date') :"";
        $data['Tend_date'] = $this->input->post('Tend_date') ? $this->input->post('Tend_date') :"";
        $data['Estart_date'] = $this->input->post('Estart_date') ? $this->input->post('Estart_date') :"";
        $data['Eend_date'] = $this->input->post('Eend_date') ? $this->input->post('Eend_date') :"";
        //list
        $this->db->select('a.*,b.rel_name');
        $this->db->from('finance a');
        $this->db->join('user b','a.user_id = b.id','inner');
        $this->db->join('user c','a.create_user = c.id','inner');
        if($this->input->post('user_id')){
            $this->db->where('a.user_id',$this->input->post('user_id'));
        }
        if($this->input->post('status')){
            $this->db->where('a.status',$this->input->post('status'));
        }
        if($this->input->post('finance_num')){
            $this->db->like('a.finance_num',trim($this->input->post('finance_num')));
        }
        if($this->input->post('borrower_name')){
            $this->db->like('a.borrower_name',trim($this->input->post('borrower_name')));
        }
        if($this->input->POST('company_id')) {
            $this->db->where('a.company_id', $this->input->POST('company_id'));
        }
        if($this->input->POST('subsidiary_id')) {
            $this->db->where_in('a.subsidiary_id', $this->input->POST('subsidiary_id'));
        }
        if($this->input->POST('Cstart_date')) {
            $this->db->where('date_format(a.create_date, \'%Y-%m-%d\') >=', $this->input->POST('Cstart_date'));
        }
        if($this->input->POST('Cend_date')) {
            $this->db->where('date_format(a.create_date, \'%Y-%m-%d\') <=', $this->input->POST('Cend_date'));
        }
        if($this->input->POST('Tstart_date')) {
            $this->db->where('a.tijiao_date >=', $this->input->POST('Tstart_date'));
        }
        if($this->input->POST('Tend_date')) {
            $this->db->where('a.tijiao_date <=', $this->input->POST('Tend_date'));
        }
        if($this->input->POST('Estart_date')) {
            $this->db->where('a.end_date >=', $this->input->POST('Estart_date'));
        }
        if($this->input->POST('Eend_date')) {
            $this->db->where('a.end_date <=', $this->input->POST('Eend_date'));
        }
        //$this->db->where('a.flag',1);
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        // $data['type_list'] = $this->db->from('question_type')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }
}
