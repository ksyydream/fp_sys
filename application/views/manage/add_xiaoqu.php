<style type="text/css">
.file-box{ position:relative;width:340px}
.btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
.file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_fp_xiaoqu');?>" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
        	<fieldset>
        	<legend>分店信息</legend>
				<dl>
					<dt>小区名称：</dt>
					<dd>
						<input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
						<input name="name" type="text" class="required" value="<?php if(!empty($name)) echo $name;?>" />
					</dd>
				</dl>
				<dl>
					<dt>别名：</dt>
					<dd>
						<input name="other_name" type="text" value="<?php if(!empty($other_name)) echo $other_name;?>" />
					</dd>
				</dl>
				<dl>
					<dt>小区地址：</dt>
					<dd>
						<input name="address" type="text" class="required" value="<?php if(!empty($address)) echo $address;?>" />
					</dd>
				</dl>
				<dl>
					<dt>区镇：</dt>
					<dd>
						<select name="area_id" class="combox">
							<?php
							if (!empty($area_list)):
								foreach ($area_list as $row):
									$selected = $row->id == $area_id ? "selected" : "";
									?>
									<option value="<?php echo $row->id; ?>" <?php echo $selected; ?>><?php echo $row->area; ?></option>
									<?php
								endforeach;
							endif;
							?>
						</select>
				</dl>
				<dl>
					<dt>状态：</dt>
					<dd>
						<label>
							<input
								<?php if (!empty($flag) && $flag==1){
									echo "checked";
								} ?>
								name="flag" type="checkbox">启用
						</label>

					</dd>
				</dl>
        	</fieldset>
        </div>
        <div class="formBar">
    		<ul>
    			<li><div class="buttonActive"><div class="buttonContent"><button type="submit" class="icon-save">保存</button></div></div></li>
				<li><div class="button"><div class="buttonContent"><button type="button" class="close icon-close">取消</button></div></div></li>
    		</ul>
        </div>
	</form>
</div>
<script>

</script>
