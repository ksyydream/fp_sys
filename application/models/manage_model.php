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
              where  a.role_id > 0
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
        $this->db->where('a.role_id >', 0);
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
     *
     * ***************************************以下为风控卡,清单卡一级选项*******************************************************************
     */


    public function list_product_first(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('product_first');
        if($this->input->post('first_name'))
            $this->db->like('first_name',$this->input->post('first_name'));

        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        $data['first_name'] = $this->input->post('first_name')?$this->input->post('first_name'):null;
        //list
        $this->db->select();
        $this->db->from('product_first');
        if($this->input->post('first_name'))
            $this->db->like('first_name',$this->input->post('first_name'));

        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function save_product_first(){
        //die(var_dump($this->input->post()));
        $data = array(
            "first_name"=>trim($this->input->post('first_name')),
            "flag"=>$this->input->post('flag'),
            "cdate"=>date('Y-m-d H:i:s')
        );
        if($data['first_name']=="")
            return -2;
        if($id = $this->input->post('id')){
            unset($data['cdate']);
            $row = $this->db->select()->from('product_first')->where('first_name',$data['first_name'])->where('id <>',$id)->get()->row();
            if(!$row){
                $this->db->where('id',$id)->update('product_first',$data);
            }else {
                return -1;
            }
        }else{
            $row = $this->db->select()->from('product_first')->where('first_name',$data['first_name'])->get()->row();
            if(!$row){
                $this->db->insert('product_first',$data);
            }else{
                return -1;
            }
        }
        return 1;
    }

    public function get_product_first($id){
        $row = $this->db->select()->from('product_first')->where('id',$id)->get()->row_array();
        return $row;
    }

    public function delete_product_first($id){
        $row = $this->db->select()->from('product_first')->where('id',$id)->get()->row_array();
        if(!$row)
            return -1;
        if($row['flag']==1){
            $this->db->where('id',$id)->update('product_first',array('flag'=>-1));
        }else{
            $this->db->where('id',$id)->update('product_first',array('flag'=>1));
        }
        return 1;
    }

    public function get_first_list(){
        $list = $this->db->from('product_first')->where('flag',1)->get()->result_array();
        return $list;
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
    public function list_fp_area(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('fp_area');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('fp_area');
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
    public function save_fp_area(){
        $this->db->trans_start();
        $data = array(
            'id'=>$this->input->post('id'),
            'area'=>$this->input->post('area'),
            'hot'=>$this->input->post('hot'),
            'hot_class'=>$this->input->post('hot_class'),
            'area_ratio'=>$this->input->post('area_ratio')
        );
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('fp_area', $data);
        }else{//新增
            unset($data['id']);
            $this->db->insert('fp_area', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 获取区镇详情
     */
    public function get_fp_area($id){
        $this->db->select('*')->from('fp_area')->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }

    /**
     * 获取物业列表
     */
    public function list_fp_wy(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 20;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('fp_wy');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('fp_wy');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    /**
     * 保存物业类型
     */
    public function save_fp_wy(){
        $this->db->trans_start();
        $data = array(
            'id'=>$this->input->post('id'),
            'flag'=>$this->input->post('flag'),
            'ratio'=>$this->input->post('ratio')
        );
        switch ($data['flag']){
            case 1:
                $data['max_c'] = $this->input->post('max_c');
                $data['min_c'] = $this->input->post('min_c');
                $data['mm_ratio'] = $this->input->post('mm_ratio');
                if(!$data['max_c'] || !$data['max_c'] || !$data['max_c']){
                    return '信息缺失!';
                }
                break;
            case 2:
                break;
            default:
                return '物业类别异常!';
                break;
        }
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('fp_wy', $data);
        }else{//新增
            unset($data['id']);
            $this->db->insert('fp_wy', $data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return $this->db_error;
        } else {
            return 1;
        }
    }

    /**
     * 获取物业类型详情
     */
    public function get_wy($id){
        $this->db->select('*')->from('fp_wy')->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }


    /**
     * 获取小区列表
     */
    public function list_fp_xiaoqu(){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 50;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : 1;

        //获得总记录数
        $this->db->select('count(distinct(a.id)) as num');
        $this->db->from('fp_xiaoqu a');
        $this->db->join('fp_xiaoqu_price a1','a.id = a1.xiaoqu_id','left');
        $this->db->join('fp_wy b','a1.wy_id = b.id','left');
        $this->db->join('fp_area c','a.area_id = c.id','left');

        if($this->input->post('flag'))
            $this->db->where("a.flag",$this->input->post('flag'));
        if($this->input->post('area_id'))
            $this->db->where("a.area_id",$this->input->post('area_id'));
        if($this->input->post('wy_id'))
            $this->db->where("a1.wy_id",$this->input->post('wy_id'));
        if($this->input->post('name')){

            $this->db->where("(a.name like '%" . $this->input->post('name') . "%' or a.other_name like '%" . $this->input->post('name') . "%')",null,false);
        }
        $rs_total = $this->db->get()->row();
        //die(var_dump($this->db->last_query()));
        //总记录数
        $data['countPage'] = $rs_total->num;
        $data['flag'] = $this->input->post('flag')?trim($this->input->post('flag')):null;
        $data['name'] = $this->input->post('name')?trim($this->input->post('name')):null;
        $data['area_id'] = $this->input->post('area_id') ? trim($this->input->post('area_id')):null;
        $data['wy_id'] = $this->input->post('wy_id') ? trim($this->input->post('wy_id')):null;
        //list
        $this->db->select('a.*,c.area,
        group_concat(distinct(b.wy) order by b.wy) wy_list
        ');
        $this->db->from('fp_xiaoqu a');
        $this->db->join('fp_xiaoqu_price a1','a.id = a1.xiaoqu_id','left');
        $this->db->join('fp_xiaoqu_price a2','a.id = a2.xiaoqu_id','left');
        $this->db->join('fp_wy b','a2.wy_id = b.id','left');
        $this->db->join('fp_area c','a.area_id = c.id','left');
        $this->db->where('a.id >=',1);
        if($this->input->post('flag'))
            $this->db->where("a.flag",$this->input->post('flag'));
        if($this->input->post('area_id'))
            $this->db->where("a.area_id",$this->input->post('area_id'));
        if($this->input->post('wy_id'))
            $this->db->where("a1.wy_id",$this->input->post('wy_id'));
        if($this->input->post('name')){
            $this->db->where("(a.name like '%" . $this->input->post('name') . "%' or a.other_name like '%" . $this->input->post('name') . "%')",null,false);
        }
        $this->db->group_by('a.id');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.id', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'asc');
        $data['res_list'] = $this->db->get()->result();
        $data['area_list'] = $this->db->from('fp_area')->get()->result();
        $data['wy_list'] = $this->db->from('fp_wy')->get()->result();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function get_area_list(){
        return $this->db->select()->from('fp_area')->get()->result();
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
