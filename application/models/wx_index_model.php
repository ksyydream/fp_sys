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

    public function api_user_info(){
        $openid = $this->session->userdata('openid');
        $check_ = $this->db->select()->from('fp_wx_user')->where('openid', $openid)->get()->row_array();
        if($check_){
            if($check_['true_mobile'] == ""){
                $check_['success'] = false;
            }else{
                $check_['success'] = true;
            }
            return $check_;
        }else{
            return array(
                'success' => false,
                'true_mobile' => '',
                'true_company' => '',
                'true_name' => ''
            );
        }
    }


    public function begin_cal(){
        $param = array(
            'biz_code' => 'loan',
            'account_name' => $this->input->post('user_name') ? $this->input->post('user_name') : '',
            'id_number' => $this->input->post('user_card') ? $this->input->post('user_card') : '',
            'account_mobile' => $this->input->post('user_mobile') ? $this->input->post('user_mobile') : ''
        );
        $curlPost = $param;
        header("Content-Type: text/html;charset=utf-8");
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, "https://apitest.tongdun.cn/bodyguard/apply/v4.3?partner_code=funmall&partner_key=" . $this->config->item('partner_key') ."&app_name=funmall_web"); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlPost));
        $data = curl_exec($ch); //运行curl
        //$data = iconv("utf-8", "GBK//ignore", $data);
        curl_close($ch);
        $res = json_decode($data);
       return $res;

    }
}