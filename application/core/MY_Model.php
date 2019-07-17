<?php
/**
 * 扩展模型
 *
 * 提供了大部分读写数据库的功能，继承后可以直接使用，降低模型的代码量
 * @package		app
 * @subpackage	Libraries
 * @category	model
 * @author		yaobin<645894453@qq.com>
 *
 */
class MY_Model extends CI_Model{
    public $model_success = array('status' => 1, 'msg' => '', 'result' => array());
    public $model_fail = array('status' => -1, 'msg' => '操作失败!', 'result' => array());
    protected $db_error = "数据操作发生错误，请稍后再试-_-!";
    /**
     * 构造函数
     *
     * 加载数据库和日志类
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * 把数据写入表
     * @param string $table 表名
     * @param array $arr 待写入数据
     */
    protected function create($table,$arr)
    {
        return $this->db->insert($table, $arr);
    }

    /**
     * 根据id读取一条记录
     * @param string $table 读取的表
     * @param int $id 主键id
     * @return array 一条记录信息数组
     */
    protected function read($table,$id)
    {
        return $this->db->get_where($table, array('id' => $id))->row_array();
    }

    /**
     * 根据id读取一条记录
     * @param string $table 读取的表
     * @param int $id_name 主键id的名称
     * @param int $id_value 主键id的值
     * @return array 一条记录信息数组
     */
    protected function readByID($table, $id_name, $id_value)
    {
        return $this->db->get_where($table, array($id_name => $id_value))->row_array();
    }

    /**
     * 按id返回指定列，id可以是批量
     * @param string $select 指定字段，例:'title, content, date'
     * @param string $table 查询的目标表
     * @param int,array $id 主键id，或id数组
     * @return array 返回对象数组
     */
    protected function select_where($select,$table,$id)
    {
        $this->db->select($select);
        $this->db->from($table);
        if(is_array($id)){
            $this->db->where_in('id',$id);
            return $this->db->get()->result();
        }else{
            $this->db->where('id',$id);
            return $this->db->get()->result();
        }
    }

    /**
     * 根据id更新数据
     * @param string $table 查询的目标表
     * @param int $id 主键id
     * @param array $arr 新的数据
     */
    protected function update($table,$id,$arr)
    {
        $this->db->where('id',$id);
        return $this->db->update($table,$arr);
    }

    /**
     * 删除数据
     * id可以是单个，也可以是个数组
     * @param string $table 查询的目标表
     * @param int|array $id 主键id，或id数组
     */
    protected function delete($table,$id)
    {
        if(is_array($id)){
            $this->db->where_in('id',$id);
            return $this->db->delete($table);
        }
        return $this->db->delete($table, array('id' => $id));
    }
    
    /**
     * 检测某字段是否已经存在某值
     * 
     * 存在返回该记录的信息数组，否则返回false
     * @param string $table 查询的目标表
     * @param string $field 条件字段
     * @param string $value 条件值
     * @return false,array 返回false或存在的记录信息数组
     */
    protected function is_exists($table,$field,$value)
    {
        $query = $this->db->get_where($table, array($field => $value));
        if($query->num_rows() > 0)
            return $query->row_array();
        return false;
    }

    /**
     * 分页列出数据
     * @param string $table 表名
     * @param int $limit 记录数
     * @param int $offset 偏移量
     * @param string $sort_by 排序字段 默认id
     * @param string $sort 排序 默认倒序desc,asc,random
     * @param string,null where条件，默认为空
     * @return object 返回记录对象数组
     */
    protected function list_data($table,$limit,$offset,$sort_by='id',$sort='desc',$where=null)
    {
        if(! is_null($where)) {
            $this->db->where($where);
        }
        $this->db->order_by($sort_by,$sort);
        return $this->db->get($table,$limit,$offset)->result();
    }

    /**
     * 总记录数
     * @param string $table 表名
     */
    protected function count($table)
    {
        return $this->db->count_all($table);
    }

    /**
     * 按条件统计记录
     * @param string $table 表名
     * @param string $where 条件
     */
    protected function count_where($table,$where)
    {
        $this->db->from($table);
        $this->db->where($where);
        $result = $this->db->get();
        return $result->num_rows();
    }

    /**
     * 列出全部
     * @param string $table 表名
     */
    protected function list_all($table)
    {
        return $this->db->get($table)->result();
    }

    /**
     * 列出全部根据条件
     * @param string $table 表名
     * @param string $where where条件字段
     * @param string $value where的值
     * @param string $sort_by 排序字段
     * @param string $sort 排序方式
     */
    protected function list_all_where($table,$where,$value,$sort_by='id',$sort='desc')
    {
        $this->db->from($table);
        if($where!='' and $value!=''){
            $this->db->where($where,$value);
        }
        $this->db->order_by($sort_by,$sort);
        return $this->db->get()->result();
    }

    /**
     * 列出数据（两个表关联查询）
     * @param string $select 查询字段
     * @param string $table1 表名1
     * @param string $table2 表名2
     * @param string $on 联合条件
     * @param int $limit 读取记录数
     * @param int $offset 偏移量
     * @param string $sort_by 排序字段，默认id
     * @param string $sort 排序方式，默认降序
     * @param string $where 过滤条件
     * @param string $join_type 链接方式，默认left
     */
    protected function list_data_join($select,$table1,$table2,$on,$limit,$offset,$sort_by="id",$sort='DESC',$where=null,$join_type='left')
    {
        $this->db->select($select);
        $this->db->from($table1);

        if(! is_null($where)) {
            $this->db->where($where);
        }

        $this->db->join($table2,$on,$join_type);
        $this->db->limit($limit,$offset);
        $this->db->order_by($sort_by,$sort);
        return $this->db->get()->result();
    }

    /**
     * 设置状态
     * 状态字段必须是status
     * @param string $table 表名
     * @param int $id 主键id的值
     * @param int $status 状态值
     */
    protected function set_status($table,$id,$status)
    {
        $this->db->where('id',$id);
        $this->db->set('status',$status);
        return $this->db->update($table);
    }

    /**
     * 析构函数
     *
     * 关闭数据库连接
     */
    public function __destruct()
    {
        $this->db->close();
    }
    
    ///////////////////////////////////////////////////////////////////////////////
    // WeiXin API related
    ///////////////////////////////////////////////////////////////////////////////
    public function post($url, $post_data, $timeout = 300){
    	$options = array(
    			'http' => array(
    					'method' => 'POST',
    					'header' => 'Content-type:application/json;encoding=utf-8',
    					'content' => urldecode(json_encode($post_data)),
    					'timeout' => $timeout
    			)
    	);
    	$context = stream_context_create($options);
    	return file_get_contents($url, false, $context);
    }


    /**
    public function get_access_token() {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.APP_ID.'&secret='.APP_SECRET;
        $response = file_get_contents($url);
        return json_decode($response)->access_token;
    }

    public function get_or_create_token() {

        $this->db->from('token');
        $this->db->where('app_id', APP_ID);
        $this->db->where('app_secret', APP_SECRET);
        $data_token = $this->db->get()->row_array();
        if(empty($data_token)) {
            $data = array(
                'app_id' => APP_ID,
                'app_secret' => APP_SECRET,
                'token' => $this->get_access_token(),
                'created' => time()
            );
            $this->db->insert('token', $data);
            return $data['token'];
        } else {
            $interval = time() - intval($data_token['created']);
            if($interval / 60 / 60 > 1) {
                $data_token['token'] = $this->get_access_token();
                $data_token['created'] = time();
                $this->db->where('id', $data_token['id']);
                $this->db->update('token', $data_token);
            }
            return $data_token['token'];
        }
    }
*/

    public function wxpost($template_id,$post_data,$user_id,$url_www='www.funmall.com.cn'){
        $openid = $this->get_openidByUserid($user_id);
        if($openid == -1 || empty($openid)){
            return false;
        }
        $this->load->library('wxjssdk_th',array('appid' => $this->config->item('appid'), 'appsecret' => $this->config->item('appsecret')));
        $access_token = $this->wxjssdk_th->wxgetAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;//access_token改成你的有效值

        /*$data = array(
            'first' => array(
                'value' => '数据提交成功！',
                'color' => '#FF0000'
            ),
            'keyword1' => array(
                'value' => '休假单',
                'color' => '#FF0000'
            ),
            'keyword2' => array(
                'value' => date("Y-m-d H:i:s"),
                'color' => '#FF0000'
            ),
            'remark' => array(
                'value' => '请审核！',
                'color' => '#FF0000'
            )
        );*/
        $template = array(
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url_www,
            'topcolor' => '#7B68EE',
            'data' => $post_data
        );
        $json_template = json_encode($template);
        $dataRes = $this->request_post($url, urldecode($json_template)); //这里执行post请求,并获取返回数据
        $res_ = json_decode($dataRes);
        if ($res_['errcode'] == 0) {
            return true;
        } else {
            return false;
        }

    }

/**
    public function get_token($app,$appsecret){
        $this->db->from('token');
        $this->db->where('app_id', $app);
        $this->db->where('app_secret', $appsecret);
        $data_token = $this->db->get()->row_array();
        if(empty($data_token)) {
            $data = array(
                'app_id' => $app,
                'app_secret' => $appsecret,
                'token' => $this->get_access($app,$appsecret),
                'created' => time()
            );
            $this->db->insert('token', $data);
            return $data['token'];
        } else {
            $interval = time() - intval($data_token['created']);
            if($interval / 60 / 60 > 1) {
                $data_token['token'] = $this->get_access($app,$appsecret);
                $data_token['created'] = time();
                $this->db->where('id', $data_token['id']);
                $this->db->update('token', $data_token);
            }
            return $data_token['token'];
        }
    }

    public function get_ticket($app,$appsecret){
        $this->db->from('wx_ticket');
        $this->db->where('app_id', $app);
        $this->db->where('app_secret', $appsecret);
        $data_token = $this->db->get()->row_array();
        if(empty($data_token)) {
            $data = array(
                'app_id' => $app,
                'app_secret' => $appsecret,
                'ticket' => $this->get_apiticket($app,$appsecret),
                'created' => time()
            );
            $this->db->insert('wx_ticket', $data);
            return $data['ticket'];
        } else {
            $interval = time() - intval($data_token['created']);
            if($interval / 60 / 60 > 1) {
                $data_token['ticket'] = $this->get_apiticket($app,$appsecret);
                $data_token['created'] = time();
                $this->db->where('id', $data_token['id']);
                $this->db->update('wx_ticket', $data_token);
            }
            return $data_token['ticket'];
        }
    }

    public function get_access($app,$appsecret) {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$app.'&secret='.$appsecret;
        $response = file_get_contents($url);
        return json_decode($response)->access_token;
    }

    public function get_apiticket($app,$appsecret){
        $accessToken = $this->get_token($app,$appsecret);
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        $res = json_decode($this->wxhttpGet($url));
        $ticket = $res->ticket;
        return $ticket;
    }
*/

    private function wxhttpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    public function get_openidByUserid($user_id){
        $row = $this->db->select()->from('users')->where('user_id',$user_id)->get()->row_array();
        if ($row){
            return $row['openid'];
        }else{
            return -1;
        }
    }

    function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }

    //返回失败的信息
    public function fun_fail($msg, $result = []){
        $this->model_fail['msg'] = $msg;
        $this->model_fail['result'] = $result;
        return $this->model_fail;
    }

    //返回成功的信息
    public function fun_success($msg = '操作成功', $result = []){
        $this->model_success['msg'] = $msg;
        $this->model_success['result'] = $result;
        return $this->model_success;
    }

    //验证短信
    public function check_sms($mobile, $code){
        $sms_time_out = $this->config->item('sms_time_out');
        $sms_time_out = $sms_time_out ? $sms_time_out : 120;
        $sms_log = $this->db->from('sms_log')->where(array('mobile' => $mobile, 'status' => 1))->order_by('add_time', 'desc')->limit(1)->get()->row_array();
        if(!$sms_log){
            return $this->fun_fail('请先获取验证码');
        }
        if($sms_log['code'] == $code){
            $timeOut = $sms_log['add_time'] + $sms_time_out;
            if($timeOut < time()){
                return $this->fun_fail('验证码已超时失效');
            }
        }else{
            return $this->fun_fail('验证失败,验证码有误');
        }
        return $this->fun_success('验证成功');
    }

    //将openid其他的登录状态清楚
    public function delOpenidById($id, $openid, $type){
        if($type == 'users'){
            //意味着是user登录
            $this->db->where(array('user_id <>' => $id, 'openid' => $openid))->update('users', array('openid' => ''));
            $this->db->where(array('openid' => $openid))->update('members', array('openid' => ''));
        }
        if($type == 'members'){
            //意味着是memeber登录
            $this->db->where(array('openid' => $openid))->update('users', array('openid' => ''));
            $this->db->where(array('m_id <>' => $id, 'openid' => $openid))->update('members', array('openid' => ''));
        }
    }

    //存入user的session
    public function set_user_session_wx($id){
        $this->db->from('users');
        $this->db->where('user_id', $id);
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $res = $rs->row();
            $token = uniqid();
            $user_info['wx_token'] = $token;
            $user_info['wx_user_id'] = $res->user_id;
            $user_info['wx_rel_name'] = $res->rel_name;
            $user_info['wx_user_pic'] = $res->pic;
            $user_info['wx_class'] = 'users';
            $this->session->set_userdata($user_info);
            return 1;
        }
        return -1;
    }

    //存入member的session
    public function set_member_session_wx($id){
        $this->db->from('members');
        $this->db->where('m_id', $id);
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $res = $rs->row();
            $token = uniqid();
            $member_info['wx_token'] = $token;
            $member_info['wx_m_id'] = $res->m_id;
            $member_info['wx_rel_name'] = $res->rel_name;
            $member_info['wx_user_pic'] = $res->pic;
            $member_info['wx_class'] = 'members';
            $this->session->set_userdata($member_info);
            return 1;
        }
        return -1;
    }

    //通过invite_code查询管理员
    public function getMemberByInvite($invite_code){
        $res = $this->db->select()->from('members')->where(array('status' => 1, 'invite_code' => $invite_code))->get()->row_array();
        return $res;
    }

    //同盾获取征信信息
    public function get_tongdun_info($account_name = '', $id_number = '', $accout_mobile = ''){
        $user_id = $this->session->userdata('wx_user_id');
        $this->db->set('use_td_times','use_td_times + 1',false);
        $this->db->where('user_id', $user_id);
        $this->db->update('users');
        $log_ = array(
            'account_name' => $account_name,
            'id_number' => $id_number,
            'account_mobile' => $accout_mobile,
            'user_id' => $user_id,
            'add_time' => time()
        );
        //先查看是否有缓存数据
        $tongdun_info = $this->db->select()->from('tongdun_info')->where(array('id_number' => $id_number))->order_by('add_time', 'desc')->limit(1)->get()->row_array();
        if($tongdun_info){
            $td_deadline_ = $this->config->item('td_deadline'); //缓存数据使用限期,这里是秒为单位的
            //判断是否超过使用限期,如果没有超过,就直接使用
            if($tongdun_info['add_time'] + $td_deadline_ > time()){
                $log_['td_id'] = $tongdun_info['id'];
                $this->db->insert('tongdun_log', $log_);
                return $this->fun_success('获取成功', $tongdun_info);
            }
        }
        $param = array(
            'biz_code' => 'loan',
            'account_name' => $account_name,
            'id_number' => $id_number,
            'account_mobile' => $accout_mobile
        );
        $curlPost = $param;
        header("Content-Type: text/html;charset=utf-8");
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $this->config->item('td_url') . "&partner_key=" . $this->config->item('partner_key')); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($curlPost));
        $data = curl_exec($ch); //运行curl
        //$data = iconv("utf-8", "GBK//ignore", $data);
        curl_close($ch);
        $info_ = array(
            'account_name' => $account_name,
            'id_number' => $id_number,
            'account_mobile' => $accout_mobile,
            'user_id' => $user_id,
            'add_time' => time(),
            'json_data' => $data
        );
        $this->db->insert('tongdun_info', $info_);
        $td_id_ = $this->db->insert_id();
        $log_['td_id'] = $td_id_;
        $this->db->insert('tongdun_log', $log_);
        $info_['id'] = $td_id_;
        return $this->fun_success('获取成功', $info_);

    }

    /**
     * 自动增加单号
     * @author yangyang <yang.yang@thmarket.cn>
     * @date 2019-07-10
     */
    public function get_sys_num_auto($title){
        $check_ = $this->db->select()->from('sys_num')->where('title',$title)->get()->row_array();
        if($check_){
            $this->db->where('title',$title)->set('num','num + 1',false)->update('sys_num');
            return $check_['num'];
        }else{
            $insert_data = array(
                'title' => $title,
                'num' => 2
            );
            $this->db->insert('sys_num', $insert_data);
            return 1;
        }
    }

    //微信图片上传
    public function getmedia($media_id, $finance_num, $file){
        $app = $this->config->item('appid');
        $appsecret = $this->config->item('appsecret');
        $this->load->library('wxjssdk_th',array('appid' => $app, 'appsecret' => $appsecret));
        $accessToken = $this->wxjssdk_th->wxgetAccessToken();
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$accessToken."&media_id=".$media_id;

        if (is_readable('./upload_files/' . $file) == false) {
            mkdir('./upload_files/' . $file, 0777, true);
        }
        if (is_readable('./upload_files/' . $file . '/'.$finance_num) == false) {
            mkdir('./upload_files/' . $file . '/'.$finance_num, 0777, true);
        }
        $file_name = date('YmdHis').rand(1000,9999).'.jpg';
        $targetName = './upload_files/'.$file.'/'.$finance_num.'/'.$file_name;
        //file_put_contents('/var/yy.txt', $url);

        $ch = curl_init($url); // 初始化
        $fp = fopen($targetName, 'wb'); // 打开写入
        curl_setopt($ch, CURLOPT_FILE, $fp); // 设置输出文件的位置，值是一个资源类型
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $file_name;
    }
}

/* End of file MY_Model.php */
/* Location: ./application/core/MY_Model.php */