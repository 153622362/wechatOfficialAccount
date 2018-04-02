<?php
$obj = new Respon();
$obj->sendAllGroupMessage();

class Respon{

	/**
	 * @param array $data
	 * -data
	 * -es
	 * -e
	 */
	public function test(array $data)
	{

	}
	/**
	 * 第一次接入微信开发的操作方法
	 * @return bool
	 */
	public function index()
	{
		$this->test();
		$signature = $_GET["signature"];
		$timestamp =  $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$echostr = $_GET['echostr'];
		$token = 'weixin';
		$tmpArr = array($timestamp, $nonce,$token);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		$res = $tmpStr == $signature;
		if( $res ){
			echo $echostr;
		}else{
			return $this->respon();
		}
	}

	/**
	 * 获取微信服务器
	 */
	public function weChatServiceIpAdress()
	{
		$access_token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token={$access_token}";
		$res = $this->http($url);
		$this->xvar_dump($res);
	}

	/**
	 * 向指定用户发送群发消息
	 */
	public function sendOneGroupMessage()
	{
		$access_token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$access_token}";
		$postarr = array(
			'touser'=>'',//用户openid
			'text'=>array('content'=>'message is very happy'),
			'msgtype'=>'text',//消息类型
		);
		$postJson = json_encode($postarr);
		$res = $this->http($url,'post',true,'json',$postJson);
	}

	/**
	 * 向所有用户发送消息
	 */
	public function sendAllGroupMessage()
	{
		$access_token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token={$access_token}";
		$postarr = array(
			'filter'=>['is_to_all'=>true],
			'text'=>array('content'=>'Group all message'),
			'msgtype'=>'text',//消息类型
		);
		$postJson = json_encode($postarr);
		$res = $this->http($url,'post',true,'json',$postJson);
		var_dump($res);
	}

	/**
	 * 被动消息回复
	 */
    public	function respon()
	{
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		$postObj = simplexml_load_string( $xml );
		if( strtolower( $postObj->MsgType) == 'event'){
			/**
			 * 菜单对应key的事件
			 */
			if (strtolower($postObj->EventKey == 'Rselfmenu_0_0'))
			{
				$toUser   = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time     = time();
				$msgType  =  'text';
				$content  = '呵呵哈哈哈';
				$template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
				$info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content); //替换数据
				echo $info;
			}
			//如果是关注 subscribe 事件
			if( strtolower( $postObj->Event == 'SCAN')){ //扫描关注
				//回复用户消息(纯文本格式)
				$toUser   = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time     = time();
				$msgType  =  'text';
				$content  = '一不小心就扫描到我了';
				$template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
				$info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content); //替换数据
				echo $info;


			}
			/**
			 * 未关注的用户扫描二维码关注自动回复
			 */
			if( strtolower($postObj->Event == 'subscribe') ){
				//回复用户消息(纯文本格式)
				$toUser   = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time     = time();
				$msgType  =  'text';
				$content  = '欢迎关注我们的微信公众账号啦啦啦';
				$template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
				$info     = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
				echo $info;
			}



		}

	}

	/**
	 * 获取access_token
	 * @param string $appid 微信公号上的appid
	 * @param string $appsecret 微信公号上的secret
	 * @return string
	 */
	public function getAccessToken($appid='',$appsecret='')
	{
		if ($_SESSION['access_token'] && $_SESSION['expires_in'] > time())
		{
			return $_SESSION['access_token'];
		}else{

			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
			$access_token = $this->http($url);
			$_SESSION['access_token'] = $access_token['access_token'];
			$_SESSION['expires_in'] = time()+7000;
			return $_SESSION['access_token'];
		}
	}

	/**
	 * 创建菜单
	 */
	public function createMenu($menu=[])
	{
		$access_token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
		$menu = [
			'button'=>[
				[
					'type'=>'click',
				'name'=>urlencode('一级菜单1'),
				'key' => 'rselfmenu_0_0',
				]
			]
		];
		$post_arr = urldecode(json_encode($menu));
		$res = $this->http($url,'post',true,'json',$post_arr);
	}

	/**
	 * 发送http请求
	 * @param $url
	 * @param string $type
	 * @param bool $https
	 * @param string $res
	 * @param string $arr
	 * @return mixed|string
	 */
	public function http($url,$type='get',$https=true,$res='json',$arr='')
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($https)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		if($type == 'post'){
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
		}
		$output = curl_exec($ch);
		if($res == 'json'){
			if( curl_errno($ch)){
				return curl_error($ch);
			}else{
				curl_close($ch);
				return json_decode($output,true);
			}
		}
	}

	/**
	 * 格式化调试输出
	 * @param $param 输出的参数
	 */
	public function xvar_dump($param)
	{
		echo "<pre>";
		var_dump($param);
		echo "</pre>";
	}
}


