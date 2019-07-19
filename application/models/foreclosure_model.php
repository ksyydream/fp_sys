<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/2/16
 * Time: 21:22
 */

class Foreclosure_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    //获取赎楼详情
    public function get_foreclosure($f_id){
        $this->db->select();
        $this->db->from('foreclosure');
        $this->db->where(array(
            'foreclosure_id' => $f_id
        ));
        $f_info = $this->db->get()->row_array();
        return $f_info;
    }

    public function get_foreclosureBynowtime($now_time){
        $this->db->select();
        $this->db->from('foreclosure');
        $this->db->where(array(
            'now_time' => $now_time
        ));
        $f_info = $this->db->get()->row_array();
        return $f_info;
    }
    /**
     * 申请赎楼一
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-09
     */
    public function save_foreclosure($user_info){
        $insert_ = array(
            'borrower_marriage' => $this->input->post('borrower_marriage'),
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
        if($fc_id = $this->input->post('fc_id')){
            return $this->fun_success('借款人信息不可重复提交!', array('foreclosure_id' => $fc_id));
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

    //完善买家信息
    public function edit_foreclosure4s2(){
        $fc_id = $this->input->post('fc_id');
        if(!$fc_id)
            return $this->fun_fail('工作单异常');
        $update_ = array(
            'buyer_name' => $this->input->post('buyer_name'),
            'buyer_code' => $this->input->post('buyer_code'),
            'bank_loan_type' => $this->input->post('bank_loan_type'),
            'is_mortgage' => $this->input->post('is_mortgage'),
            'borrow_money' => $this->input->post('borrow_money'),
            'borrow_money_user' =>  $this->input->post('borrow_money'),   //备份原始
            'expect_use_time' => $this->input->post('expect_use_time'),
            'total_price' => $this->input->post('total_price'),
        );
        $where_ = array(
            'foreclosure_id' => $fc_id
        );
        $wx_class = $this->session->userdata('wx_class');
        switch($wx_class){
            case 'users':
                $update_['user_modify_time'] = time();
                break;
            case 'members':
                $update_['m_modify_time'] = time();
                break;
            default:
                return $this->fun_fail('登录状态异常');
        }


        if(!$update_['borrow_money'] || !$update_['expect_use_time'] || !$update_['total_price'] || !$update_['buyer_name'] || !$update_['buyer_code'] || !$update_['bank_loan_type'] || !$update_['is_mortgage'])
            return $this->fun_fail('信息不完善');
        if(!is_idcard($update_['buyer_code']))
            return $this->fun_fail('身份证号码不规范');
        if(!in_array($update_['bank_loan_type'], array(1,2,3))){
            return $this->fun_fail('请选择贷款方式');
        }
        if($update_['bank_loan_type'] == 2 && !in_array($update_['is_mortgage'], array(1,-1))){
            return $this->fun_fail('商业贷款需要选择按揭情况!');
        }
        $res = $this->db->where($where_)->update('foreclosure', $update_);
        if($res){
            $foreclosure_info = $this->db->select('foreclosure_id,bank_loan_type')->from('foreclosure')->where($where_)->get()->row_array();
            return $this->fun_success('操作成功', $foreclosure_info);
        }else{
            return $this->fun_fail('操作失败!');
        }

    }

    //贷款信息
    public function edit_foreclosure4s3(){
        $fc_id = $this->input->post('fc_id');
        if(!$fc_id)
            return $this->fun_fail('工作单异常');
        $f_info_ = $this->get_foreclosure($fc_id);
        if(!$f_info_)
            return $this->fun_fail('工作单异常');
        if($f_info_['bank_loan_type'] == 1){
            return $this->fun_fail('一次性付款,不需要维护此页面,请退出后重新维护');
        }
        $update_ = array(
            'old_loan_balance' => trim($this->input->post('old_loan_balance')),
            'old_loan_setup' => trim($this->input->post('old_loan_setup')),
            'deposit' => trim($this->input->post('deposit')),
        );
        $where_ = array(
            'foreclosure_id' => $fc_id
        );
        $wx_class = $this->session->userdata('wx_class');
        switch($wx_class){
            case 'users':
                $update_['user_modify_time'] = time();
                break;
            case 'members':
                $update_['m_modify_time'] = time();
                break;
            default:
                return $this->fun_fail('登录状态异常');
        }


        if(!$update_['old_loan_balance'] || !$update_['old_loan_setup'] || !$update_['deposit'])
            return $this->fun_fail('信息不完善');

        if($f_info_['bank_loan_type'] == 2 && $f_info_['is_mortgage'] == 1){
            $update_['is_repayment'] = trim($this->input->post('is_repayment'));
            switch($update_['is_repayment']){
                case 1:
                    $update_['repayment_money'] = trim($this->input->post('repayment_money'));
                    if(!$update_['repayment_money']){
                        return $this->fun_fail('请维护进入金额!');
                    }
                    break;
                case -1:
                    break;
                default:
                    return $this->fun_fail('请维护首付是否进入还款');
            }
            $update_['mortgage_bank'] = trim($this->input->post('mortgage_bank'));
            if(!$update_['mortgage_bank']){
                return $this->fun_fail('请维护买家按揭银行!');
            }
            $update_['mortgage_money'] = trim($this->input->post('mortgage_money'));
            if(!$update_['mortgage_money']){
                return $this->fun_fail('请维护买家按揭金额!');
            }
        }else{
            $update_['expect_mortgage_bank'] = trim($this->input->post('expect_mortgage_bank'));
            if(!$update_['expect_mortgage_bank']){
                return $this->fun_fail('请维护买家预计按揭银行!');
            }
            $update_['expect_mortgage_money'] = trim($this->input->post('expect_mortgage_money'));
            if(!$update_['expect_mortgage_money']){
                return $this->fun_fail('请维护买家预计按揭金额!');
            }
        }
        $res = $this->db->where($where_)->update('foreclosure', $update_);
        if($res){
            $foreclosure_info = $this->db->select('foreclosure_id,bank_loan_type')->from('foreclosure')->where($where_)->get()->row_array();
            return $this->fun_success('操作成功', $foreclosure_info);
        }else{
            return $this->fun_fail('操作失败!');
        }
    }

    //保存身份证照片
    public function edit_foreclosure4s4(){
        $file_ = 'foreclosure';
        $fc_id = $this->input->post('fc_id');
        if(!$fc_id)
            return $this->fun_fail('工作单异常');
        $f_info_ = $this->get_foreclosure($fc_id);
        $update_ = array();
        $where_ = array(
            'foreclosure_id' => $fc_id
        );
        $wx_class = $this->session->userdata('wx_class');
        switch($wx_class){
            case 'users':
                $update_['user_modify_time'] = time();
                break;
            case 'members':
                $update_['m_modify_time'] = time();
                break;
            default:
                return $this->fun_fail('登录状态异常');
        }
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
        if($f_info_['borrower_marriage'] == 1){
            if($borrower_spouse_img_SFZ = $this->input->post('borrower_spouse_img_SFZ')){
                $update_['borrower_spouse_img_SFZ'] = $this->getmedia($borrower_spouse_img_SFZ, $f_info_['work_no'], $file_);
                if(!@file_get_contents('./upload_files/' . $file_. '/'. $f_info_['work_no'] . '/' . $update_['borrower_spouse_img_SFZ'])){
                    return $this->fun_fail('请上传配偶身份证');
                }
            }else{
                if(!@file_get_contents('./upload_files/' . $file_ . '/'. $f_info_['work_no'] . '/'  . $f_info_['borrower_spouse_img_SFZ'])){
                    return $this->fun_fail('请上传配偶身份证!');
                }
            }
        }

        $res = $this->db->where($where_)->update('foreclosure', $update_);
        if($res){
            $foreclosure_info = $this->db->select('foreclosure_id')->from('foreclosure')->where($where_)->get()->row_array();
            return $this->fun_success('操作成功', $foreclosure_info);
        }else{
            return $this->fun_fail('操作失败!');
        }
    }

    //获取赎楼房产证照片
    public function get_property_img($fc_id = 0){
        $img_list = $this->db->from('foreclosure_property_img')->where('fc_id', $fc_id)->order_by('sort_id','asc')->get()->result_array();
        return $img_list;
    }

    //获取赎楼房产证照片
    public function get_credit_img($fc_id = 0){
        $img_list = $this->db->from('foreclosure_credit_img')->where('fc_id', $fc_id)->order_by('sort_id','asc')->get()->result_array();
        return $img_list;
    }

    //保存房产证照片
    public function edit_foreclosure4s5(){
        $file_ = 'foreclosure';
        $fc_id = $this->input->post('fc_id');
        if(!$fc_id)
            return $this->fun_fail('工作单异常');
        $f_info_ = $this->get_foreclosure($fc_id);
        if(!$f_info_)
            return $this->fun_fail('工作单异常');
        $update_ = array();
        $where_ = array(
            'foreclosure_id' => $fc_id
        );
        $wx_class = $this->session->userdata('wx_class');
        switch($wx_class){
            case 'users':
                $update_['user_modify_time'] = time();
                break;
            case 'members':
                $update_['m_modify_time'] = time();
                break;
            default:
                return $this->fun_fail('登录状态异常');
        }
        $img_insert_ = array();
        $old_imgs = $this->input->post('old_img');
        $wx_imgs = $this->input->post('wx_img');
        if(!$old_imgs && !$wx_imgs){
            return $this->fun_fail('房产证照片需要上传');
        }
        $sort_id_ = 1;
        if($old_imgs){
            foreach($old_imgs as $img_){
                if(@file_get_contents('./upload_files/' . $file_. '/'. $f_info_['work_no'] . '/' . $img_)){
                    $img_insert_[] = array(
                        'fc_id'         => $fc_id,
                        'file_name'     => $img_,
                        'add_time'      => time(),
                        'sort_id'       => $sort_id_++
                    );
                }
            }
        }
        if($wx_imgs){
            foreach($wx_imgs as $media_){
                $wx_img_ = $this->getmedia($media_, $f_info_['work_no'], $file_);
                if(@file_get_contents('./upload_files/' . $file_. '/'. $f_info_['work_no'] . '/' . $wx_img_)){
                    $img_insert_[] = array(
                        'fc_id'         => $fc_id,
                        'file_name'     => $wx_img_,
                        'add_time'      => time(),
                        'sort_id'       => $sort_id_++
                    );
                }
            }
        }
        if(!$img_insert_)
            return $this->fun_fail('房产证照片需要上传');
        $this->db->trans_start();
        $this->db->where('fc_id', $fc_id)->delete('foreclosure_property_img');
        $this->db->insert_batch('foreclosure_property_img', $img_insert_);
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return $this->fun_fail('操作失败!');
        }else{
            $res = $this->db->where($where_)->update('foreclosure', $update_);
            if($res){
                $foreclosure_info = $this->db->select('foreclosure_id')->from('foreclosure')->where($where_)->get()->row_array();
                return $this->fun_success('操作成功', $foreclosure_info);
            }else{
                return $this->fun_fail('操作失败!');
            }
        }


    }

    //保存征信照片
    public function edit_foreclosure4s6(){
        $file_ = 'foreclosure';
        $fc_id = $this->input->post('fc_id');
        if(!$fc_id)
            return $this->fun_fail('工作单异常');
        $f_info_ = $this->get_foreclosure($fc_id);
        if(!$f_info_)
            return $this->fun_fail('工作单异常');
        $update_ = array();
        $where_ = array(
            'foreclosure_id' => $fc_id
        );
        $wx_class = $this->session->userdata('wx_class');
        switch($wx_class){
            case 'users':
                $update_['user_modify_time'] = time();
                $update_['submit_time'] = time();
                $update_['status'] = 2;
                break;
            case 'members':
                $update_['m_modify_time'] = time();
                break;
            default:
                return $this->fun_fail('登录状态异常');
        }
        $img_insert_ = array();
        $old_imgs = $this->input->post('old_img');
        $wx_imgs = $this->input->post('wx_img');
        if(!$old_imgs && !$wx_imgs){
            return $this->fun_fail('征信报告需要上传');
        }
        $sort_id_ = 1;
        if($old_imgs){
            foreach($old_imgs as $img_){
                if(@file_get_contents('./upload_files/' . $file_. '/'. $f_info_['work_no'] . '/' . $img_)){
                    $img_insert_[] = array(
                        'fc_id'         => $fc_id,
                        'file_name'     => $img_,
                        'add_time'      => time(),
                        'sort_id'       => $sort_id_++
                    );
                }
            }
        }
        if($wx_imgs){
            foreach($wx_imgs as $media_){
                $wx_img_ = $this->getmedia($media_, $f_info_['work_no'], $file_);
                if(@file_get_contents('./upload_files/' . $file_. '/'. $f_info_['work_no'] . '/' . $wx_img_)){
                    $img_insert_[] = array(
                        'fc_id'         => $fc_id,
                        'file_name'     => $wx_img_,
                        'add_time'      => time(),
                        'sort_id'       => $sort_id_++
                    );
                }
            }
        }
        if(!$img_insert_)
            return $this->fun_fail('征信报告需要上传');
        $this->db->trans_start();
        $this->db->where('fc_id', $fc_id)->delete('foreclosure_credit_img');
        $this->db->insert_batch('foreclosure_credit_img', $img_insert_);
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return $this->fun_fail('操作失败!');
        }else{
            $res = $this->db->where($where_)->update('foreclosure', $update_);
            if($res){
                $foreclosure_info = $this->db->select('foreclosure_id')->from('foreclosure')->where($where_)->get()->row_array();
                return $this->fun_success('操作成功', $foreclosure_info);
            }else{
                return $this->fun_fail('操作失败!');
            }
        }


    }

    //获取赎楼列表信息 users专用
    public function get_list4users(){
        $user_id = $this->session->userdata('wx_user_id');
        if(!$user_id)
            return array();
        $page = $this->input->post('page') ? $this->input->post('page') : 1;
        $limit_ = 5;
        $status_type_ = $this->input->post('status_type') ? $this->input->post('status_type') : 0;
        $this->db->select();
        $this->db->from('foreclosure');
        $this->db->where('user_id', $user_id);
        switch($status_type_){
            case 1:
                //草稿箱
                $this->db->where('status', 1);
                break;
            case 2:
                //待审核
                $this->db->where('status', 2);
                break;
            case 3:
                //审核通过
                $this->db->where_in('status', array(3, 4));
                break;
            case -1:
                //审核失败 ,包括同盾审核失败 和 总监审核失败
                $this->db->where_in('status', array(-1, -2));
                break;
            default:
                $this->db->where_in('status', array(1, 2, 3, 4, -1, -2));
        }
        $this->db->limit($limit_, ($page - 1) * $limit_ );
        $res = $this->db->order_by('add_time', 'desc')->get()->result_array();
        $fc_deadline_ = $this->config->item('fc_deadline'); //缓存数据使用限期,这里是秒为单位的
        foreach($res as $k_ => $item){
            if($item['add_time'] + $fc_deadline_ < time()){
                $res[$k_]['gq_flag'] = 1;   //过期标记位
                $res[$k_]['show_msg'] = '已过期';
            }else{
                $diff_ = $item['add_time'] + $fc_deadline_ - time();
                $day_ = intval($diff_ / (60 * 60 * 24));
                $remain = $diff_ % 86400;
                $hours = intval($remain/3600);
                $res[$k_]['gq_flag'] = -1;  //过期标记位
                $res[$k_]['show_msg'] = '<span>剩余天数：</span>' . $day_ . '天' . $hours . '小时';
            }
        }
        return $res;
    }

    //获取赎楼列表信息 members专用
    public function get_list4members(){
        $m_id = $this->session->userdata('wx_m_id');
        if(!$m_id)
            return array();
        //需要知道 管理员的级别
        $member_info_ = $this->db->select()->from('members')->where('m_id', $m_id)->get()->row_array();
        if(!$member_info_)
            return array();
        $page = $this->input->post('page') ? $this->input->post('page') : 1;
        $limit_ = 5;
        $status_type_ = $this->input->post('status_type') ? $this->input->post('status_type') : 0;
        $this->db->select('fc.*, us.mobile us_mobile_, us.rel_name us_rel_name_');
        $this->db->from('foreclosure fc');
        $this->db->join('users us', 'fc.user_id = us.user_id', 'left');
        if($member_info_['level'] == 3){
            $this->db->where('fc.m_id', $m_id);
        }
        if($member_info_['level'] == 2){
            $this->db->join('members m', 'm.m_id = fc.m_id', 'left');
            $this->db->where("(fc.m_id = $m_id  or m.parent_id = $m_id)");
        }

        switch($status_type_){
            case 2:
                //待审核
                $this->db->where('fc.status', 2);
                break;
            case 3:
                //审核通过
                $this->db->where_in('fc.status', array(3, 4));
                break;
            case -1:
                //审核失败 ,包括同盾审核失败 和 总监审核失败
                $this->db->where_in('fc.status', array(-1, -2));
                break;
            default:
                $this->db->where_in('fc.status', array(2, 3, 4, -1, -2));
        }
        $this->db->limit($limit_, ($page - 1) * $limit_ );
        $res = $this->db->order_by('fc.submit_time', 'desc')->get()->result_array();
        $fc_deadline_ = $this->config->item('fc_deadline'); //缓存数据使用限期,这里是秒为单位的
        foreach($res as $k_ => $item){
            if($item['add_time'] + $fc_deadline_ < time()){
                $res[$k_]['show_msg'] = '已过期';
            }else{
                $diff_ = $item['add_time'] + $fc_deadline_ - time();
                $day_ = intval($diff_ / (60 * 60 * 24));
                $remain = $diff_ % 86400;
                $hours = intval($remain/3600);
                $res[$k_]['show_msg'] = '<span>剩余天数：</span>' . $day_ . '天' . $hours . '小时';
            }
        }
        return $res;
    }

    //获取赎楼 审核时选择的材料清单
    public function get_file_list(){
        $file_list = array();
        $file_list['type_1'] = $this->db->from('file_list')->where(array('status' => 1, 'file_type' => 1, 'use_type' => 1))->get()->result_array();
        $file_list['type_2'] = $this->db->from('file_list')->where(array('status' => 1, 'file_type' => 2, 'use_type' => 1))->get()->result_array();
        $file_list['type_3'] = $this->db->from('file_list')->where(array('status' => 1, 'file_type' => 3, 'use_type' => 1))->get()->result_array();
        return $file_list;
    }

    public function get_file_listbyFid($f_id = 0){
        $file_list = array();
        $file_list['type_1'] = $this->db->select('f.*')->from('file_list f')
            ->join('foreclosure_file_list ff', 'f.id = ff.file_id', 'right')
            ->where(array('ff.fc_id' => $f_id, 'f.file_type' => 1, 'f.use_type' => 1))->get()->result_array();
        $file_list['type_2'] = $this->db->select('f.*')->from('file_list f')
            ->join('foreclosure_file_list ff', 'f.id = ff.file_id', 'right')
            ->where(array('ff.fc_id' => $f_id, 'f.file_type' => 2, 'f.use_type' => 1))->get()->result_array();
        $file_list['type_3'] = $this->db->select('f.*')->from('file_list f')
            ->join('foreclosure_file_list ff', 'f.id = ff.file_id', 'right')
            ->where(array('ff.fc_id' => $f_id, 'f.file_type' => 3, 'f.use_type' => 1))->get()->result_array();
        return $file_list;
    }

    public function foreclosure_audit($m_info){
        $f_id = $this->input->post('fc_id');
        if(!$f_id)
            return $this->fun_fail('此赎楼业务不存在!');
        $f_info = $this->get_foreclosure($f_id);
        if(!$f_info)
            return $this->fun_fail('此赎楼业务不存在!');
        if($f_info['status'] != 2)
            return $this->fun_fail('此赎楼不可审核,或已被处理!');
        if($m_info['level'] != 2)
            return $this->fun_fail('只有总监可以审核!');
        $this->load->model('wx_members_model');
        $manger_info = $this->wx_members_model->get_member_info($f_info['m_id']);
        if($f_info['m_id'] != $m_info['m_id'] && $manger_info['parent_id'] != $m_info['m_id']){
            return $this->fun_fail('您无权限审核此赎楼业务!');
        }
        $update_ = array(
            'status' => $this->input->post('status'),
            'audit_time' => time(),
            'audit_m_id' => $m_info['m_id']
        );
        switch ($update_['status']){
            case 3:
                $select_ids = $this->input->post('selected_ids');
                if(!$select_ids)
                    return $this->fun_fail('请选择准备资料清单!');
                $select_ids_arr = explode(",", $select_ids);
                $now_time = time();
                $file_ = $this->db->select("id file_id, {$f_id} fc_id, {$now_time} add_time", false)->from('file_list')->where(array('use_type' => 1, 'status' => 1))->where_in('id', $select_ids_arr)->get()->result_array();
                if(!$file_)
                    return $this->fun_fail('请选择准备资料清单!!');
                $this->db->where(array('fc_id' => $f_id))->delete('foreclosure_file_list');
                $this->db->insert_batch('foreclosure_file_list', $file_);
                break;
            case -1:
                break;
            default:
                return $this->fun_fail('请选择审核结果!');
        }
        $this->db->where(array('foreclosure_id' => $f_id, 'status' => 2))->update('foreclosure', $update_);
        if($update_['status'] == 3){
            $data_msg = array(
                'first' => array(
                    'value' => "赎楼工作单".$f_info['work_no']."审核通过!",
                    'color' => '#FF0000'
                ),
                'keyword1' => array(
                    'value' => '赎楼工作单',
                    'color' => '#FF0000'
                ),
                'keyword2' => array(
                    'value' => '审核通过',
                    'color' => '#FF0000'
                ),
                'keyword3' => array(
                    'value' => date('Y-m-d H:i:s'),
                    'color' => '#FF0000'
                ),
                'remark' => array(
                    'value' => '感谢您对我们工作的信任,请点击查看需要携带的资料!',
                    'color' => '#FF0000'
                )
            );
            $this->wxpost($this->config->item('WX_YY'), $data_msg, $f_info['user_id'], $this->config->item('img_url_DBY') . '/wx_users/foreclosure_detail7/' . $f_id);
        }
        return $this->fun_success('审核成功', array('foreclosure_id' => $f_id));
    }

    public function foreclosure_special($m_info){
        $f_id = $this->input->post('fc_id');
        if(!$f_id)
            return $this->fun_fail('此赎楼业务不存在!');
        $f_info = $this->get_foreclosure($f_id);
        if(!$f_info)
            return $this->fun_fail('此赎楼业务不存在!');
        if(!in_array($f_info['status'], array(-1)))
            return $this->fun_fail('只有同盾审核失败的才可设置 绿色通道!');
        if($m_info['level'] != 2)
            return $this->fun_fail('只有总监可以审核!');
        $this->load->model('wx_members_model');
        $manger_info = $this->wx_members_model->get_member_info($f_info['m_id']);
        if($f_info['m_id'] != $m_info['m_id'] && $manger_info['parent_id'] != $m_info['m_id']){
            return $this->fun_fail('您无权限审核此赎楼业务!');
        }
        $fc_deadline_ = $this->config->item('fc_deadline'); //缓存数据使用限期,这里是秒为单位的
        if($f_info['add_time'] + $fc_deadline_ < time()){
            return $this->fun_fail('赎楼工作单 同盾审核时效已过期!'); //如果工作单不在草稿箱内,或者已经过期,就到详情页面
        }
        $update_ = array(
            'status' => 1,
            'is_special' => 1,
            'special_time' => time()
        );
        $this->db->where(array('foreclosure_id' => $f_id, 'status' => -1))->update('foreclosure', $update_);
        return $this->fun_success('操作成功');
    }

    public function foreclosure_finish($m_info){
        $f_id = $this->input->post('fc_id');
        if(!$f_id)
            return $this->fun_fail('此赎楼业务不存在!');
        $f_info = $this->get_foreclosure($f_id);
        if(!$f_info)
            return $this->fun_fail('此赎楼业务不存在!');
        if($f_info['status'] != 3)
            return $this->fun_fail('此赎楼不可终审,或已被处理!');
        if($m_info['level'] != 1)
            return $this->fun_fail('只有总经理可以审核!');
        $update_ = array(
            'status' => 4,
            'finish_time' => time(),
            'finish_m_id' => $m_info['m_id']
        );
        $this->db->where(array('foreclosure_id' => $f_id, 'status' => 3))->update('foreclosure', $update_);
        return $this->fun_success('终审成功', array('foreclosure_id' => $f_id));
    }
}