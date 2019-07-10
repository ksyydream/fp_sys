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
        if(!is_idcard($insert_['borrower_code']))
            return $this->fun_fail('借款人身份证号码不规范');
        if(!check_mobile($insert_['borrower_mobile']))
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
                if(!is_idcard($insert_['borrower_spouse_code']))
                    return $this->fun_fail('配偶身份证号码不规范');
                if(!check_mobile($insert_['borrower_spouse_mobile']))
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
            if($json_data->success == true){
                $insert_['borrower_td_score'] = $json_data->result_desc->ANTIFRAUD->final_score;
                $insert_['borrower_td_decision'] = $json_data->result_desc->ANTIFRAUD->final_decision;
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
                if($json_data->success == true){
                    $insert_['borrower_spouse_td_score'] = $json_data->result_desc->ANTIFRAUD->final_score;
                    $insert_['borrower_spouse_td_decision'] = $json_data->result_desc->ANTIFRAUD->final_decision;
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
        //要生成工作单号
        $title_ = 'SL' . date('Ymd', time());
        $insert_['work_no'] = $title_ . sprintf('%03s', $this->get_sys_num_auto($title_));
        $this->db->insert('foreclosure', $insert_);
        $foreclosure_id = $this->db->insert_id();
        $foreclosure_info = $this->db->select('foreclosure_id')->from('foreclosure')->where('foreclosure_id', $foreclosure_id)->get()->row_array();
        return $this->fun_success('提交成功', $foreclosure_info);
    }

    public function edit_foreclosure4s2(){
        $user_id = $this->session->userdata('wx_user_id');
        $fc_id = $this->input->post('fc_id');
        if(!$fc_id)
            return $this->fun_fail('工作单异常');
        $f_info_ = $this->get_foreclosure4user($fc_id);
        if(!$f_info_ || $f_info_['status'] != 1){
            return $this->fun_fail('工作单已不在草稿箱内,不可修改');
        }
        $update_ = array(
            'buyer_name' => $this->input->post('buyer_name'),
            'buyer_code' => $this->input->post('buyer_code'),
            'bank_loan_type' => $this->input->post('bank_loan_type'),
            'is_mortgage' => $this->input->post('is_mortgage'),
            'user_modify_time' => time()
        );
        if(!$update_['buyer_name'] || !$update_['buyer_code'] || !$update_['bank_loan_type'] || !$update_['is_mortgage'])
            return $this->fun_fail('信息不完善');
        if(!is_idcard($update_['buyer_code']))
            return $this->fun_fail('身份证号码不规范');
        if(!in_array($update_['bank_loan_type'], array(1,2,3))){
            return $this->fun_fail('请选择贷款方式');
        }
        if($update_['bank_loan_type'] == 2 && !in_array($update_['is_mortgage'], array(1,-1))){
            return $this->fun_fail('商业贷款需要选择按揭情况!');
        }
        $res = $this->db->where(array(
            'foreclosure_id' => $fc_id,
            'user_id' => $user_id
        ))->update('foreclosure', $update_);
        if($res){
            $foreclosure_info = $this->db->select('foreclosure_id,bank_loan_type')->from('foreclosure')
                ->where(array(
                    'foreclosure_id' => $fc_id,
                    'user_id' => $user_id
                ))->get()->row_array();
            return $this->fun_success('操作成功', $foreclosure_info);
        }else{
            return $this->fun_fail('操作失败!');
        }

    }

    public function edit_foreclosure4s4(){
        $file_ = 'foreclosure';
        $user_id = $this->session->userdata('wx_user_id');
        $fc_id = $this->input->post('fc_id');
        if(!$fc_id)
            return $this->fun_fail('工作单异常');
        $f_info_ = $this->get_foreclosure4user($fc_id);
        if(!$f_info_ || $f_info_['status'] != 1){
            return $this->fun_fail('工作单已不在草稿箱内,不可修改');
        }
        $update_ = array(
            'user_modify_time' => time()
        );
        if($borrower_img_SFZ = $this->input->post('borrower_img_SFZ')){
            $update_['borrower_img_SFZ'] = $this->getmedia($borrower_img_SFZ, $f_info_['work_no'], $file_);
            if(!@file_get_contents('./upload_files/' . $file_. '/'. $f_info_['work_no'] . '/' . $update_['borrower_img_SFZ'])){
                return $this->fun_fail('请上传借款人身份证');
            }
        }else{
            if(!@file_get_contents('./upload_files/' . $file_ . '/'. $f_info_['work_no'] . '/'  . $f_info_['borrower_img_SFZ'])){
                return $this->fun_fail('请上传借款人身份证!');
            }
        }
        if($f_info_['is_mortgage'] == 1){
            if($borrower_spouse_img_SFZ1 = $this->input->post('borrower_spouse_img_SFZ1')){
                $update_['borrower_spouse_img_SFZ1'] = $this->getmedia($borrower_spouse_img_SFZ1, $f_info_['work_no'], $file_);
                if(!@file_get_contents('./upload_files/' . $file_. '/'. $f_info_['work_no'] . '/' . $update_['borrower_spouse_img_SFZ1'])){
                    return $this->fun_fail('请上传配偶身份证');
                }
            }else{
                if(!@file_get_contents('./upload_files/' . $file_ . '/'. $f_info_['work_no'] . '/'  . $f_info_['borrower_spouse_img_SFZ1'])){
                    return $this->fun_fail('请上传配偶身份证!');
                }
            }
        }

        $res = $this->db->where(array(
            'foreclosure_id' => $fc_id,
            'user_id' => $user_id
        ))->update('foreclosure', $update_);
        if($res){
            $foreclosure_info = $this->db->select('foreclosure_id')->from('foreclosure')
                ->where(array(
                    'foreclosure_id' => $fc_id,
                    'user_id' => $user_id
                ))->get()->row_array();
            return $this->fun_success('操作成功', $foreclosure_info);
        }else{
            return $this->fun_fail('操作失败!');
        }
    }

    //获取赎楼详情,这里做用户权限判断
    public function get_foreclosure4user($f_id){
        $this->db->select();
        $this->db->from('foreclosure');
        $this->db->where(array(
            'foreclosure_id' => $f_id,
            'user_id' => $this->session->userdata('wx_user_id')
        ));
        $f_info = $this->db->get()->row_array();
        return $f_info;
    }

}