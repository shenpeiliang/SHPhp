<?php
namespace Controller;
/**
 * 接口验证操作类
 * 必须传递的字段信息：'data','time','source','sign'
 * 进行加密的字段信息：'data', 'time', 'source','authkey'
 * 加密规则：加密字段先按字典排序，然后再进行sha1加密
 * @author shenpeiliang
 * 20170803
 */
class ApiController{
	/**
	 * 接口超时限制-发送时间和当前时间进行对比 默认5分钟
	 */
	const  TIME_EXCEED = 300;
	/**
	 * 来源 个别方法可能只允许某来源访问
	 * @var unknown
	 */
	protected  $_source;
	/**
	 * 解析json后的数据
	 * @var array
	 */
	protected  $_data;  
	/**
	 * 配置
	 * @var unknown
	 */
	protected $_config = array();
	
	public function __construct(){
		$this->_init();
		$this->auth();
	}
	/**
	 * 初始化配置
	 */
	private function _init(){
		$this->_config = array(
			//ip白名单
			'allowed_ips' => array(
				'127.0.0.*',
				'192.168.*'
			),
			'authkey' => 'redis#key#api#version_1',
			'source' => array(
				'10086',
				'10000'
			),
		);
	}
	/**
	 * 验证
	 */
	protected function auth(){
		$ip = $this->get_client_ip();		
		$passed = false;
		foreach ($this->_config['allowed_ips'] as $pattern){
			if(fnmatch($pattern, $ip)){
				$passed = true;
				break;	
			}
		}	
		if(!$passed){
			$this->ajax_response(false,'IP_NOT_ALLOWED');
		}	
		//必须字段
		$required = ['data','time','source','sign'];
		foreach($required as $item){
			if(!isset($_POST[$item])){
				$this->ajax_response(false, strtoupper($item).'_INVALID');
			}
		}
		//请求数据
		$data = $_POST['data'];//json数据
		$time = $_POST['time'];//时间戳
		$this->_source = $source = $_POST['source'];//来源
		$sign = $_POST['sign'];//签名
		
		//检查来源
		if (! in_array($source, $this->_config['source'])) {
			$this->ajax_response(false, 'SOURCE_INVALID');
		}
		
		// 检查时间戳
		if (abs($time - time()) > self::TIME_EXCEED) {
			$this->ajax_response(false, 'TIME_EXPIRED');
		}
		
		// 检查签名
		$authkey = $this->_config['authkey'];
		//需要签名加密的字段值
		$args = compact ( 'data', 'time', 'source','authkey');
		ksort($args);  //按数组的键排序
		$signature = ''; //需要签名加密组合的字符串
		foreach ($args as $key => $val){
			$signature .= $key . '=' . $val; 
		}
		//sha1加密
		$signature = sha1($signature);
		//验证是否通过
		if ($sign !== $signature) {
			$this->ajax_response(false, 'SIGN_MISMATCH');
		}
		
		// 解码请求数据
		$this->_data = json_decode($data, true);
		
		//json_decode出错时返回null或false
		if ($this->_data === null || $this->_data === false) {
			$this->ajax_response(false, 'DATA_INVALID');
		}
	}
	/**
	 * ajax返回 (输出并终止执行)
	 *
	 * @param bool $success
	 * @param string $code 错误代码
	 * @param mixed $data 附加数据
	 * @return void
	 */
	public function ajax_response($success, $code = NULL, $data = NULL) {
		$success = ( bool ) $success;
		if ($data === NULL && is_array ( $code )) {
			isset ( $code ['data'] ) and $data = $code ['data'];
			isset ( $code ['code'] ) and $code = $code ['code'];
		}
		$code === NULL and $code = '';
		die ( json_encode ( compact ( 'success', 'code', 'data' ) ) );
	}
	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 ;1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	public function get_client_ip($type = 0,$adv=false) {
		$type       =  $type ? 1 : 0;
		static $ip  =   NULL;
		if ($ip !== NULL) return $ip[$type];
		if($adv){
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$pos    =   array_search('unknown',$arr);
				if(false !== $pos) unset($arr[$pos]);
				$ip     =   trim($arr[0]);
			}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip     =   $_SERVER['HTTP_CLIENT_IP'];
			}elseif (isset($_SERVER['REMOTE_ADDR'])) {
				$ip     =   $_SERVER['REMOTE_ADDR'];
			}
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u",ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
}
$obj = new ApiController();