<?php
echo '<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>';
class Respon{
	public  $appid =  '';
	public  $appsecret = '';
	/**
	 * 第一次接入微信开发的操作方法
	 * @return bool
	 */
	public function index()
	{
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
			'touser'=>'otwmk1VlGXmBUP_5kqCGaeyby24s',//用户openid
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
		$appid = $this->appid;
		$appsecret = $this->appsecret;
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
	public function createMenu()
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

//	--------------获取网页授权
	/**
	 * 获取openid
	 */
	public function getopenid()
	{
		$appid = $this->appid;
		$redirect_uri = urlencode('http://abc.freeif.cn');//获取到的code返回这个链接上回调链接,与微信后台设置的JS接口网址一样
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
		header('location:'.$url);
	}

	/**
	 *	getopenid方法跳转的地址用code换取网页授权access_token
	 */
	public function openidCodeGetAccessToken()
	{
		$appid = $this->appid;
		$appsecret = $this->appsecret;
		$code = $_GET['code'];
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code ';
		$res = $this->http($url);
		var_dump($res);
	}

	/**
	 * 获取用户信息可以与getopenid方法整合
	 */
	public function getUserInfo()
	{
		$appid = $this->appid;
		$redirect_uri = urlencode('http://kwx6uw.natappfree.cc'); //获取到的code返回到这个链接上
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
		header('location:'.$url);
	}

	/**
	 * getUserInfo方法跳转的地址code换取网页授权access_token
	 * 拉去用户信息
	 */
	public function usetCodeGetAccessToken()
	{
		$appid = $this->appid;
		$appsecret = $this->appsecret;
		$code = $_GET['code'];

		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code ';
		$res = $this->http($url);
		//拉取用户信息
		$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$res['access_token'].'&openid='.$res['openid'].'&lang=zh_CN ';
		$info = $this->http($url);
		var_dump($info);
	}
//________________获取网页授权end

//	-----------------------------JS-SDK
	/**
	 * 获取jsapi_ticket
	 * @return mixed
	 */
	public function getJsApiTicket()
	{
		if($_SESSION['jsapi_ticket_expire_time'] > time() && $_SESSION['jsapi_ticket']){
			$jsapi_ticket = $_SESSION['jspai_ticket'];
		}else{
			$access_token = $this->getAccessToken();

			$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
			$res = $this->http($url);
			$jsapi_ticket = $res['ticket'];
			$_SESSION['jsapi_ticket_expire_time'] = time() + 7000;
			$_SESSION['jsapi_ticket'] = $jsapi_ticket;
		}
		return $jsapi_ticket;
	}

	/**
	 * 获取签名
	 * 1.生成签名
	 * 2.把数据返回到显示页面
	 */
	public function getSignature()
	{
		//1.获取jsapi_ticket票据
		$jsapi_ticket = $this->getJsApiTicket();
		$time = time();
		$noncestr = 'Wm3WZYTPz0wzccnW';
		$url = 'http://kwx6uw.natappfree.cc/'; //一定要有/不然Msg错误
		//2.获取signature
		$signature = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$time.'&url='.$url;
		$signature = sha1($signature);
		echo "<title>分享接口</title>";
		echo "<script>
				wx.config({
				debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
				appId: '{$this->appid}', // 必填，公众号的唯一标识
				timestamp: {$time}, // 必填，生成签名的时间戳
				nonceStr: '{$noncestr}', // 必填，生成签名的随机串
				signature: '{$signature}',// 必填，签名，见附录1
				jsApiList: [
					'onMenuShareTimeline',
					'onMenuShareAppMessage',
					'scanQRCode',
			] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
			});
			wx.ready(function(){
			wx.onMenuShareTimeline({
			title: 'imooc', // 分享标题
			link: 'http://www.imooc.com', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
			imgUrl: 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1509517687451&di=0e632a8f620b74408d0706803ff1e333&imgtype=0&src=http%3A%2F%2Fg.hiphotos.baidu.com%2Fzhidao%2Fwh%253D600%252C800%2Fsign%3D4db4202e8d5494ee8777071f1dc5ccc6%2F6159252dd42a28348af1979a5ab5c9ea14cebfc8.jpg', // 分享图标
			success: function () { 
				alert('success');
			},
			cancel: function () { 
				alert('fail');
			}
			});
			wx.onMenuShareAppMessage({
				title: 'friend', // 分享标题
				desc: 'share friend', // 分享描述
				link: 'http://www.baidu.com', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
				imgUrl: 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1509517687451&di=0e632a8f620b74408d0706803ff1e333&imgtype=0&src=http%3A%2F%2Fg.hiphotos.baidu.com%2Fzhidao%2Fwh%253D600%252C800%2Fsign%3D4db4202e8d5494ee8777071f1dc5ccc6%2F6159252dd42a28348af1979a5ab5c9ea14cebfc8.jpg', // 分享图标
				type: 'link', // 分享类型,music、video或link，不填默认为link
				dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
				success: function () { 
					alert('success2'); //分享给朋友成功的时候会自动触发
				},
				cancel: function () { 
					alert('fail2'); //分享给朋友失败的时候会自动触发
				}
			});
			
			});
		function scan()
		{
		wx.scanQRCode({
			needResult: 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
			scanType: [\"qrCode\",\"barCode\"], // 可以指定扫二维码还是一维码，默认二者都有
			success: function (res) {
			var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
		}
		});
		}
		
			wx.error(function(res){
			});
			</script>";
		echo "<button onclick='scan();'>scan</button>";
	}
//	----------------------------JS-SDK-END
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


