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
				<dl class="nowrap">
					<dt>新增物业类型：</dt>
					<dd> <select class="combox" id="selectwy">
							<?php
							if (!empty($wy_list)):
								foreach ($wy_list as $row):
									?>
									<option value="<?php echo $row['id']; ?>" ><?php echo $row['wy']; ?></option>
									<?php
								endforeach;
							endif;
							?>
						</select><input type="button" id="add_wy" value="新增"></dd>
				</dl>
        	</fieldset>
			<fieldset>
				<table class="list nowrap" width="600px" >
					<thead>
					<tr>
						<th width="200">物业类型</th>
						<th width="300">基准评估价</th>
						<th width="30">操作</th>
					</tr>
					</thead>
					<tbody class="tbody" id="file_list_wb">
					<?php if(!empty($list)):?>
						<?php foreach($list as $k1=>$v1):?>
								<tr class="unitBox" data_id="<?php echo $v->id;?>" id="<?php echo "olda".$v->id;?>">
									<td><input type="text" size='30' readonly="readonly" name="wy_name[]" value="<?php echo $v1->wy?>"></td>
									<td style="display: none;"><input type="hidden" size='30' name="wy_id[]" value="<?php echo $v1->id?>"></td>
									<td><input type="number" class="required" size='10' name="price[]" value="<?php echo $v1->price?>"></td>
									<td><a class="btnDel" href="javascript:$('#olda<?php echo $v1->id;?>').remove();void(0);"><span>删除</span></a></td>
								</tr>
						<?php endforeach;?>
					<?php endif;?>
					</tbody>
				</table>
			</fieldset>
        </div>
        <div class="formBar">
    		<ul>
    			<li><div class="buttonActive"><div class="buttonContent"><button type="submit" onclick="return check_input();" class="icon-save">保存</button></div></div></li>
				<li><div class="button"><div class="buttonContent"><button type="button" class="close icon-close">取消</button></div></div></li>
    		</ul>
        </div>
	</form>
</div>
<script>
	function check_input(){
		flag = 1;
		$("#file_list_wb").find('[name="price[]"]').each(function(){
			var regSalary = /^[1-9]+\d*$/;
			if (!regSalary.test($(this).val())) {
				flag=2;
				alertMsg.warn('请填写正确的数字');
				return false
			}
		})
		if(flag==2){
			return false;
		}
	}
	$("#add_wy").click(function(){
		var flag = 1;
		if($("#selectwy").val()=="" || $("#selectwy").val()==null){
			alertMsg.warn('您未选择物业类型,请确认选择后新增！')
			return false;
		}
		$("#file_list_wb").find(".unitBox").each(function(){
			if($("#selectwy").val() == $(this).attr('data_id')){
				alertMsg.warn('您选择的颜色,已添加过！')
				flag = 2;
				return false;
			}
		})

		if(flag ==2){
			return false;
		}
		var html = "<tr class='unitBox' data_id='" + $("#selectwy").val() + "' id='olda" + $("#selectwy").val() + "'>" +
			"<td><input type='text' readonly='readonly' size='30' name='wy_name[]' value='" + $("#selectwy").find("option:selected").text() + "'></td>" +
			"<td style='display: none;'><input type='hidden'  size='30' name='wy_id[]' value='" + $("#selectwy").val() + "'></td>" +
			"<td><input type='number' class='required' size='10' name='price[]'' value=''></td>" +
			"<td><a class='btnDel' href='javascript:$(\"#olda" + $("#selectwy").val() + "\").remove();void(0);'><span>删除</span></a></td>" +
			"</tr>";

		$("#file_list_wb").append(html);

	})

</script>
