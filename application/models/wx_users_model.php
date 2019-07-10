<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/9/16
 * Time: 13:40
 */

class Wx_users_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_user_info($user_id){
        return $this->db->select()->from('users')->where('user_id', $user_id)->get()->row_array();
    }

    /**
     * 申请赎楼一
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-09
     */
    public function save_foreclosure($user_info){
        $insert_ = array(
            'borrower_marriage' => $this->input->post('is_marriage'),
            'borrower_name' => $this->input->post('borrower_name'),
            'borrower_code' => $this->input->post('borrower_code'),
            'borrower_mobile' => $this->input->post('borrower_mobile'),
            'now_time' => $this->input->post('now_time'),
            'user_id' => $user_info['user_id'],
            'm_id' => $user_info['invite'],
            'add_time' => time(),
            'status' => 1,
        );
        //查看是否重复提交
        $check_info_ = $this->db->select()->from('foreclosure')->where(array('user_id' => $user_info['user_id'], 'now_time' => $insert_['now_time']))->get()->row_array();
        if($check_info_){
            return $this->fun_success('借款人信息不可重复提交!', $check_info_);
        }
        if(!$insert_['borrower_name'] || !$insert_['borrower_code'] || !$insert_['borrower_mobile'])
            return $this->fun_fail('借款人信息不完善');
        if(is_idcard($insert_['borrower_code']))
            return $this->fun_fail('借款人身份证号码不规范');
        if(check_mobile($insert_['borrower_mobile']))
            return $this->fun_fail('借款人电话号码不规范');
        //开始效验
        switch($insert_['borrower_marriage']){
            case -1:
                break;
            case 1:
                $insert_['borrower_spouse_name'] = $this->input->post('borrower_spouse_name');
                $insert_['borrower_spouse_mobile'] = $this->input->post('borrower_spouse_mobile');
                $insert_['borrower_spouse_code'] = $this->input->post('borrower_spouse_code');
                if(!$insert_['borrower_spouse_name'] || !$insert_['borrower_spouse_mobile'] || !$insert_['borrower_spouse_code'])
                    return $this->fun_fail('配偶信息不完善');
                if(is_idcard($insert_['borrower_spouse_code']))
                    return $this->fun_fail('配偶身份证号码不规范');
                if(check_mobile($insert_['borrower_spouse_mobile']))
                    return $this->fun_fail('配偶电话号码不规范');
                break;
            default:
                return $this->fun_fail('婚姻状况未完善');
        }
        /** 此处需要调取同盾数据
         先通过身份证获取数据库内的同盾数据,
         如果数据库内没有数据,或者数据已经过期(过期时间暂定7天,建议过期时间存config文件内),再调取同盾接口获取借款人及其配偶的信息
         */
        //先给默认分数
        $insert_['borrower_td_score'] = -1;
        $insert_['borrower_td_decision'] = '';
        $insert_['borrower_td_id'] = -1;
        $borrower_td_info_ = $this->get_tongdun_info($insert_['borrower_name'], $insert_['borrower_code'], $insert_['borrower_mobile']);
        if($borrower_td_info_ && $borrower_td_info_['status'] == 1){
            $td_info = $borrower_td_info_['result'];
            $insert_['borrower_td_id'] = $td_info['id'];
            $json_data = json_decode($td_info['json_data']);
            if($json_data['success'] == true){
                $insert_['borrower_td_score'] = $json_data['result_desc']['ANTIFRAUD']['final_score'];
                $insert_['borrower_td_decision'] = $json_data['result_desc']['ANTIFRAUD']['final_decision'];
            }
        }

        if($insert_['borrower_marriage'] == 1){
            $insert_['borrower_spouse_td_id'] = -1;
            $insert_['borrower_spouse_td_score'] = -1;
            $insert_['borrower_spouse_td_decision'] = '';
            $borrower_spouse_td_info_ = $this->get_tongdun_info($insert_['borrower_spouse_name'], $insert_['borrower_spouse_code'], $insert_['borrower_spouse_mobile']);
            if($borrower_spouse_td_info_ && $borrower_spouse_td_info_['status'] == 1){
                $td_info = $borrower_spouse_td_info_['result'];
                $insert_['borrower_spouse_td_id'] = $td_info['id'];
                $json_data = json_decode($td_info['json_data']);
                if($json_data['success'] == true){
                    $insert_['borrower_spouse_td_score'] = $json_data['result_desc']['ANTIFRAUD']['final_score'];
                    $insert_['borrower_spouse_td_decision'] = $json_data['result_desc']['ANTIFRAUD']['final_decision'];
                }
            }
        }
        //REJECT、REVIEW、PASS 的同盾信息中 REVIEW、PASS 算通过
        //开始效验
        switch($insert_['borrower_marriage']){
            case -1:
                if(!in_array($insert_['borrower_td_decision'], array('REVIEW', 'PASS'))){
                    $insert_['status'] = -1;
                    $insert_['td_status'] = -1;
                }else{
                    $insert_['status'] = 1;
                    $insert_['td_status'] = 2;
                }
                break;
            case 1:
                if(!in_array($insert_['borrower_td_decision'], array('REVIEW', 'PASS')) || !in_array($insert_['borrower_spouse_td_decision'], array('REVIEW', 'PASS'))){
                    $insert_['status'] = -1;
                    $insert_['td_status'] = -1;
                }else{
                    $insert_['status'] = 1;
                    $insert_['td_status'] = 2;
                }
                break;
        }
        $this->db->insert('foreclosure', $insert_);
        $foreclosure_id = $this->db->insert_id();
        $foreclosure_info = $this->db->select()->from('foreclosure')->where('foreclosure_id', $foreclosure_id)->get()->row_array();
        return $this->fun_success('提交成功', $foreclosure_info);
    }

}