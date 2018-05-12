<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="renderer" content="webkit" />
<title><?php echo $this->config->item('app_name');?></title>
<link href="<?php echo base_url();?>dwz/themes/css/login.css" rel="stylesheet" type="text/css" />

<script src="<?php echo base_url();?>dwz/js/jquery-1.7.2.min.js" type="text/javascript"></script>
</head>

<body>
	<div id="login">
		<div id="login_header">
			<span class="logo">
				<h1>房猫后台管理系统</h1>
			</span>
			<div class="login_headerContent">
				<div class="navList">
					<ul>
						<li><a href="javascript:;">设为首页</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div id="login_content">
			<div class="loginForm">
				<form method="post" action="<?php echo site_url('manage_login/check_login');?>">
					<font color="red">
		                <?php if(!empty($login_errors)) echo $login_errors; ?>
	                </font>
					<p>
						<label>登录用户：</label>
						<input type="text" name="username" size="20" value="<?php echo set_value('username'); ?>" class="login_input" />
					</p>
					<p>
						<label>登录密码：</label>
						<input type="password" name="password" size="20" value="<?php echo set_value('password'); ?>" class="login_input" />
					</p>
					<div class="login_bar">
						<input class="sub" type="submit" value=" " id="sub" />
					</div>
				</form>
			</div>
			<div class="login_banner"><img src="<?php echo base_url();?>dwz/themes/default/images/login_banner.jpg" /></div>
			<div class="login_main">
				<ul class="helpList">
					<li><a href="#">忘记密码怎么办？</a></li>
					<li><a href="#">为什么登录失败？</a></li>
				</ul>
				<div class="login_inner">
					<p></p>
				</div>
			</div>
		</div>
		<div id="login_footer">
			Copyright &copy;
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
$(function(){
	if ($.browser.msie && ($.browser.version == "6.0") && !$.support.style) {
	    alert('请使用推荐浏览器(chrome,firefox,ie9+)访问系统！');
	    $('input').attr('readonly','readonly');
	}
});
</script>