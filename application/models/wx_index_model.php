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
        $this->db->select("b.id,b.name,(case 
    when b.other_name = '' then b.name else b.other_name END) as other_name",false);
        $this->db->from('fp_xiaoqu_price a');
        $this->db->join('fp_xiaoqu b','a.xiaoqu_id = b.id','left');
        if($data['keyword']){
           // $this->db->group_start();
            $this->db->where("(b.name like '%" . $data['keyword'] . "%' or b.other_name like '%" . $data['keyword'] . "%')",null,false);

           // $this->db->group_end();
        }else{
            $this->db->where('a.id', -1);
        }
        $this->db->where('b.flag',1);
        $this->db->group_by('b.id,b.name,b.other_name');
        $this->db->order_by('b.name','desc');
        $this->db->limit(10, 0);
        $data = $this->db->get()->result_array();

        return $data;
    }

    public function api_get_xiaoqu_info(){
        $xiaoqu_id = trim($this->input->post('xiaoqu_id')) ? trim($this->input->post('xiaoqu_id')) : '';
        $this->db->select("a.id,b.name,b.address,a.price,c.wy,d.area,c.flag,c.min_c,c.max_c,d.area_ratio",false);
        $this->db->from('fp_xiaoqu_price a');
        $this->db->join('fp_xiaoqu b','a.xiaoqu_id = b.id','left');
        $this->db->join('fp_wy c','a.wy_id = c.id','left');
        $this->db->join('fp_area d','b.area_id = d.id','left');
        $this->db->where('b.id',$xiaoqu_id);
        $data = $this->db->get()->result_array();

        return $data;
    }

    public function api_get_price4jq(){
        $res_data = array(
            'err_msg' => '',
            'success' => false
        );
        $price_id = $this->input->post('price_id');
        if(!$price_id){
            $res_data['err_msg'] = '请先选择小区';
            return $res_data;
        }
        $status = $this->input->post('status');
        if(!$status || !in_array($status,array(1,2))){
            $res_data['err_msg'] = '请先选择小区';
            return $res_data;
        }
        $price_info = $this->db->select('a.*,c.area_ratio,c.hot_class,c.hot,d.flag,d.ratio,d.mm_ratio,d.min_c,d.max_c')->from('fp_xiaoqu_price a')
            ->join('fp_xiaoqu b','a.xiaoqu_id = b.id','inner')
            ->join('fp_area c','c.id = b.area_id','inner')
            ->join('fp_wy d','d.id = a.wy_id','inner')
            ->where('a.id', $price_id)->get()->row_array();
        if(!$price_info){
            $res_data['err_msg'] = '请先选择小区';
            return $res_data;
        }
        $res_data['price'] = $price_info['price']; //基准评估价
        $res_data['price'] *= $price_info['area_ratio']; //乘以区域系数
        $res_data['flag'] = $price_info['flag']; //物业类型
        $res_data['hot'] = $price_info['hot']; //区域热度
        $res_data['hot_class'] = $price_info['hot_class']; //区域热度
        switch ($status){
            case 1:

                break;
            case 2:
                $mianji = $this->input->post('mianji');
                if(!$mianji){
                    $res_data['err_msg'] = '请填写面积';
                    return $res_data;
                }
                $zlc = $this->input->post('zlc');
                if(!$zlc){
                    $res_data['err_msg'] = '请填写总楼层';
                    return $res_data;
                }
                $res_data['price'] *= $price_info['ratio']; //乘以物业类型系数，因为除了别墅该系数均是1，所以可以直接乘
                if($price_info['flag'] == 1){
                    $szlc = $this->input->post('szlc');
                    if(!$szlc){
                        $res_data['err_msg'] = '请先填写所在楼层';
                        return $res_data;
                    }
                    if($zlc > $price_info['max_c'] || $zlc < $price_info['min_c']){
                        $res_data['err_msg'] = '总楼层应该在'.$price_info['min_c'].'到' . $price_info['max_c'] . '之间';
                        return $res_data;
                    }
                    if($szlc > $zlc){
                        $res_data['err_msg'] = '所在楼层不可大于总楼层!';
                        return $res_data;
                    }
                    //楼层系数
                    if($szlc == 1 || $szlc == $zlc){
                        $res_data['price'] *= $price_info['mm_ratio']; //乘以顶底楼系数
                        if($szlc == 1){
                            $res_data['lc_class'] = 'em-floor-tag5';
                            $res_data['lc_name'] = '底层';
                        }
                        if($szlc == $zlc){
                            $res_data['lc_class'] = 'em-floor-tag4';
                            $res_data['lc_name'] = '顶层';
                        }
                    }else{
                        $xs_ = $szlc / $zlc;
                        $xs_ = floor($xs_ * 10) / 10;
                        $lc_info = $this->db->select()->from('fp_ratio')
                            ->where(array(
                                'min_c <=' => $xs_,
                                'max_c >=' => $xs_,
                                'class' => 1
                            ))->get()->row_array();
                        if(!$lc_info){
                            $res_data['err_msg'] = '楼层信息异常!';
                            return $res_data;
                        }
                        $res_data['price'] *= $lc_info['ratio']; //乘以楼层系数
                        $res_data['lc_class'] = $lc_info['class_name'];
                        $res_data['lc_name'] = $lc_info['remark'];
                    }
                    //面积系数
                    $mj_info = $this->db->select()->from('fp_ratio')
                        ->where(array(
                            'min_c <=' => $mianji,
                            'max_c >=' => $mianji,
                            'class' => 2
                        ))->get()->row_array();
                    if(!$mj_info){
                        $res_data['err_msg'] = '面积信息异常!';
                        return $res_data;
                    }
                    $res_data['mj_class'] = $mj_info['class_name'];
                    $res_data['mj_name'] = $mj_info['remark'];
                    $res_data['price'] *= $mj_info['ratio']; //乘以面积系数


                }
                break;
            default:
                $res_data['err_msg'] = '请先选择小区';
                return $res_data;
        }

        $res_data['price'] = floor($res_data['price']);
        //die(var_dump($res_data['price']));
        $res_data['success'] = true;
        $this->save_pg_log($status,$price_id,$res_data['price'],$this->input->post('mianji'),$this->input->post('zlc'),$this->input->post('szlc'));
        return $res_data;
    }

    public function save_pg_log($status,$price_id,$price_log,$mianji = null, $zlc = null, $szlc = null){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        $price_info = $this->db->select('b.*,c.area,d.flag,d.wy,a.price,c.area_ratio')->from('fp_xiaoqu_price a')
            ->join('fp_xiaoqu b','a.xiaoqu_id = b.id','inner')
            ->join('fp_area c','c.id = b.area_id','inner')
            ->join('fp_wy d','d.id = a.wy_id','inner')
            ->where('a.id', $price_id)->get()->row_array();
        if($check_ && $price_info){
            $insert_data = array(
                'name' => $price_info['name'],
                'other_name' => $price_info['other_name'],
                'address' => $price_info['address'],
                'wy' => $price_info['wy'],
                'area' => $price_info['area'],
                'wx_id' => $check_['id'],
                'status' => $status
            );
            switch ($status){
                case 1:
                    if($mianji && $price_info['flag'] == 2){
                        $insert_data['mianji'] = $mianji;
                    }
                    break;
                case 2:
                    $insert_data['mianji'] = $mianji;
                    $insert_data['zlc'] = $zlc;
                    $insert_data['szlc'] = $szlc ? $szlc : -1;
                    break;
                default:
                    return -1;
            }
            $check_pg = $this->db->select()->from('fp_pg_log')->where($insert_data)->get()->row_array();
            $insert_data['price_1'] = $price_info['price'] * $price_info['area_ratio'];
            $insert_data['price_log'] = $price_log;
            $insert_data['flag'] = $price_info['flag'];
            $insert_data['price_id'] = $price_id;
            $insert_data['cdate'] = date('Y-m-d H:i:s',time());
            if($check_pg){
                $this->db->where('id',$check_pg['id'])->update('fp_pg_log', $insert_data);
            }else{
                $this->db->insert('fp_pg_log', $insert_data);
            }
            return 1;
        }
        return -1;
    }

    public function save_person_name(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        if($check_){
            if($true_name = $this->input->post('true_name')){
                $res = $this->db->where('id', $check_['id'])->update('fp_wx_user', array('true_name' => $true_name));
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function save_person_tel(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        if($check_){
            if($true_mobile = $this->input->post('true_mobile')){
                $res = $this->db->where('id', $check_['id'])->update('fp_wx_user', array('true_mobile' => $true_mobile));
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function save_person_company(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        if($check_){
            if($true_company = $this->input->post('true_company')){
                $res = $this->db->where('id', $check_['id'])->update('fp_wx_user', array('true_company' => $true_company));
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function save_person_opinion(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        if($check_){
            if($moblie = $this->input->post('moblie') && $remark = $this->input->post('remark')){
                $this->db->insert('fp_opinion',array(
                    'mobile' => $moblie,
                    'remark' => $remark,
                    'cdate' => date('Y-m-d H:i:s',time()),
                    'wx_id' => $check_['id']
                ));
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function get_pg_log_list(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        $data['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
        $data['count'] = isset($_GET['count']) ? $_GET['count'] : 10;
        $data['start'] = ($data['page'] - 1) * $data['count'];


        $this->db->select('count(1) num');
        $this->db->from('fp_pg_log');
        $this->db->where('wx_id', $check_['id']);
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['total'] = $rs_total->num;
        //list
        $this->db->select("id,wy,name,
CASE WHEN status = 1 and flag = 1 then '快评' 
    WHEN status = 2 and flag = 1 then  '精评' 
    else '' end as status_name,DATE_FORMAT(cdate,'%Y/%m/%d') cdate_day",false);
        $this->db->from("fp_pg_log");
        $this->db->where('wx_id', $check_['id']);
        $this->db->limit($data['count'], $data['start']);
        $this->db->order_by('cdate','desc');
        $this->db->order_by('id','desc');
        $data['events'] = $this->db->get()->result_array();
        return $data;
    }

    public function person_pg_histroy_detail($id){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        $data = $this->db->select('*,round(price_1) as price_kp,round(price_log) as price_jp')->from('fp_pg_log')->where(array('id' => $id , 'wx_id' => $check_['id']))->get()->row_array();
        return $data;
    }
}