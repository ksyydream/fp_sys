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

    public function issubordinates($parent_id,$subordinates_id){
    if(!$parent_id || !$subordinates_id){
        return 2;
    }
    $parent_row = $this->db->select('b.permission_id,a.company_id')->from('user a')
        ->join('role b','a.role_id = b.id','left')
        ->where('a.id',$parent_id)->get()->row_array();
    $parent_sub = $this->db->select('b.*')->from('user a')
        ->join('user_subsidiary b','a.id = b.user_id','left')
        ->where('a.id',$parent_id)->get()->result_array();
    $user_row = $this->db->select('b.permission_id,c.subsidiary_id,a.company_id')->from('user a')
        ->join('role b','a.role_id = b.id','left')
        ->join('user_subsidiary c','a.id = c.user_id','left')
        ->where('a.id',$subordinates_id)->get()->row_array();
    //这里判断是否有审核权限
    if($parent_row['permission_id'] > 4){
        return 2;
    }
    //这里判断职级是否满足要求
    /*if($parent_row['role_id'] < $user_row['role_id']){
        return 2;
    }*/
    //如果是管理员直接通过
    if(in_array($parent_row['permission_id'],array(1))){
        return 1;
    }
    //如果是总经理,判断是否是同一个公司
    if(in_array($parent_row['permission_id'],array(2))){
        if($parent_row['company_id'] == $user_row['company_id']){
            return 1;
        }else{
            return 2;
        }
    }
    //如果是区域经理,店长,副店长,店秘 需要判断是否是同一个公司,同一个部门
    if(in_array($parent_row['permission_id'],array(3,4))){
        if($parent_row['company_id'] == $user_row['company_id']){
            foreach($parent_sub as $item){
                if($item['subsidiary_id'] == $user_row['subsidiary_id']){
                    return 1;
                }
            }
            return 2;
        }else{
            return 2;
        }
    }


}

    public function issubordinates_age($parent_id,$subordinates_id){
        if(!$parent_id || !$subordinates_id){
            return 2;
        }
        $parent_row = $this->db->select('b.permission_id,a.company_id')->from('user a')
            ->join('role b','a.role_id = b.id','left')
            ->where('a.id',$parent_id)->get()->row_array();
        $parent_sub = $this->db->select('b.*')->from('user a')
            ->join('user_subsidiary b','a.id = b.user_id','left')
            ->where('a.id',$parent_id)->get()->result_array();
        $user_row = $this->db->select('b.permission_id,a.company_id')->from('user a')
            ->join('role b','a.role_id = b.id','left')
            ->where('a.id',$subordinates_id)->get()->row_array();
        $user_sub = $this->db->select('b.*')->from('user a')
            ->join('user_subsidiary b','a.id = b.user_id','left')
            ->where('a.id',$subordinates_id)->get()->result_array();
        //这里判断是否有审核权限
        if($parent_row['permission_id'] > 4){
            return 2;
        }
        //这里判断职级是否满足要求
        if($parent_row['permission_id'] > $user_row['permission_id']){
            return 2;
        }
        //如果是管理员直接通过
        if(in_array($parent_row['permission_id'],array(1))){
            return 1;
        }
        //如果是总经理,判断是否是同一个公司
        if(in_array($parent_row['permission_id'],array(2))){
            if($parent_row['company_id'] == $user_row['company_id']){
                return 1;
            }else{
                return 2;
            }
        }
        //如果是区域经理,店长,副店长,店秘 需要判断是否是同一个公司,同一个部门
       if(in_array($parent_row['permission_id'],array(3,4))){
            if($parent_row['company_id'] == $user_row['company_id']){
                foreach($parent_sub as $item){
                    foreach($user_sub as $item1){
                        if($item['subsidiary_id'] == $item1['subsidiary_id']){
                          /*  echo $item['subsidiary_id'];
                            echo $item1['subsidiary_id'];
                            die();*/
                            return 1;
                        }
                    }

                }
                return 2;
            }
        }
        return 2;

    }

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

    public function wxpost($template_id,$post_data,$user_id,$url='www.funmall.com.cn'){
        $openid = $this->get_openid($user_id);
        if($openid == -1 || empty($openid)){
            return false;
        }
        $access_token = $this->get_or_create_token();
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
            'url' => $url,
            'topcolor' => '#7B68EE',
            'data' => $post_data
        );
        $json_template = json_encode($template);
        $dataRes = $this->request_post($url, urldecode($json_template)); //这里执行post请求,并获取返回数据
      /*  if($this->session->userdata('login_user_id')==24){
            die(var_dump($dataRes));
        }*/

        if ($dataRes['errcode'] == 0) {
            return true;
        } else {
            return false;
        }

    }

    public function wxpost_fin($template_id,$post_data,$user_id,$url_www='www.funmall.com.cn'){
        $this->load->config('wxpay_config');
        $openid = $this->get_openid($user_id);
        if($openid == -1 || empty($openid)){
            return false;
        }
        $access_token = $this->get_token($this->config->item('fin_appid'),$this->config->item('fin_appsecret'));
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;//access_token改成你的有效值


        $template = array(
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url_www,
            'topcolor' => '#7B68EE',
            'data' => $post_data
        );
        $json_template = json_encode($template);
        $dataRes = $this->request_post($url, urldecode($json_template)); //这里执行post请求,并获取返回数据
        /*  if($this->session->userdata('login_user_id')==24){
              die(var_dump($dataRes));
          }*/

        if ($dataRes['errcode'] == 0) {
            return true;
        } else {
            return false;
        }

    }

    public function wxpost_finByOpenid($template_id,$post_data,$openid,$url_www='www.funmall.com.cn'){
        $this->load->config('wxpay_config');
        $access_token = $this->get_token($this->config->item('fin_appid'),$this->config->item('fin_appsecret'));
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;//access_token改成你的有效值


        $template = array(
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url_www,
            'topcolor' => '#7B68EE',
            'data' => $post_data
        );
        $json_template = json_encode($template);
        $dataRes = $this->request_post($url, urldecode($json_template)); //这里执行post请求,并获取返回数据
        /*  if($this->session->userdata('login_user_id')==24){
              die(var_dump($dataRes));
          }*/

        if ($dataRes['errcode'] == 0) {
            return true;
        } else {
            return false;
        }

    }

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

    public function get_openid($user_id){
        $row = $this->db->select()->from('user')->where('id',$user_id)->get()->row_array();
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

    public function check_sum($company_id){
        $row = $this->db->select()->from('company')->where('id',$company_id)->get()->row_array();
        if(!$row){
            return -1;
        }
        if($row['sum'] > $this->config->item('Arrears_CK')){
            return 1;
        }else{
            return -1;
        }
    }

    public function change_sum($company_id,$qty,$style,$demo,$table=null,$t_id=-1,$flag=1){
        $res_sum = $this->check_sum($company_id);
        if($res_sum == 1 || $style == 1 || $flag == 2){
            if($style ==1){
                $this->db->set('sum','sum + '.$qty,false);
            }else{
                $this->db->set('sum','sum - '.$qty,false);
            }
            $this->db->where('id',$company_id);
           $up_flag = $this->db->update('company');
            if(!$up_flag){
                return -1;
            }
            $data = array(
                'company_id' => $company_id,
                'qty' => $qty,
                'style' => $style,
                'demo' => $demo,
                'user_id' => $this->session->userdata('login_user_id'),
                't_id' => $t_id,
                't_name'=>$table,
                'flag'=>1,
                'created' => date("Y-m-d H:i:s")
            );
           $in_flag = $this->db->insert('sum_log',$data);
            if(!$in_flag){
                return -1;
            }
            return 1;
        }else{
            return -1;
        }

    }
}

/* End of file MY_Model.php */
/* Location: ./application/core/MY_Model.php */