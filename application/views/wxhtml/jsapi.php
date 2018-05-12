

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
	<script type="text/javascript" src="/static/js/jquery-1.7.2.min.js"></script>
    <title>微信支付</title>
	<?php
	$jsApiParameters = $parameters;//参数赋值
	?>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				//alert(res.err_msg);
				if(res.err_msg == "get_brand_wcpay_request:ok" ){

					$.get('<?php echo site_url('wxserver/notify_tb').'/'.$pubid;?>',function(ret){
						if(ret==1){
							alert('支付成功');
							//成功后返回我的订单页面
							location.href='<?php echo base_url().'wxserver/bdwx/';?>';
						}else{
							if(ret==-3){
								alert('支付成功');
								//成功后返回我的订单页面
								location.href='<?php echo base_url().'wxserver/bdwx/';?>';
							}else{
								alert('订单支付异常!')
							}
						}
					});

				}else
				{
					alert('支付失败');
				}
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>

</head>
<body>
    <br/>
    <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px">1分</span>钱</b></font><br/><br/>
	<div align="center">
		<button style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
	</div>
</body>
</html>