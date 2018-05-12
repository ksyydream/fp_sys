<style type="text/css">
.file-box{ position:relative;width:300px}
.btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
.file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:270px }

</style>
<div class="pageContent">
    <form method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_pg');?>" class="pageForm required-validate" onsubmit="return iframeCallback(this, navTabAjaxDone);">
        <div class="pageFormContent" layoutH="55">
        	<fieldset style="width: 95%">
        	    <dl>
        			<dt>小区名称：</dt>
        			<dd>
						<input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
						<input name="xiaoqu" type="text" class="required" value="<?php if(!empty($xiaoqu)) echo $xiaoqu;?>" />
        			</dd>

        		</dl>

				<dl>
					<dt>乡镇：</dt>
					<dd><select class="combox" name="area_id">
							<?php foreach($list_area as $v):?>
								<option value="<?php echo $v->id?>" <?php if(!empty($area_id)){if($v->id==$area_id) {echo "selected";}} ?>><?php echo $v->name?></option>
							<?php endforeach;?>
						</select></dd>
				</dl>

				<dl>
					<dt>小区信息状态：</dt>
					<dd>
						<select name="flag" class="combox">
							<option value="1" <?php if(!empty($flag) && $flag == 1) echo 'selected="selected"';?>>使用</option>
							<option value="-1" <?php if(!empty($flag) && $flag == -1) echo 'selected="selected"';?>>禁用</option>
						</select>
					</dd>
				</dl>

        	</fieldset>

			<fieldset>
				<input type="button" value="添加评估价" id="add_pgj">
				<table class="list nowrap" width="100%" >
					<thead>
					<tr>
						<th type="text" width="80"  size="30">类型</th>
						<th type="file_class" size="10" width="120">价格</th>
						<th type="del" width="30">操作</th>
					</tr>
					</thead>
					<tbody class="tbody" id="file_list">
					<?php if(!empty($list)):?>
						<?php foreach($list as $k=>$v):?>
							<tr class="unitBox">
								<td>
									<select class="combox" name="type_id[]">
										<?php foreach($list_type as $v1):?>
											<option value="<?php echo $v1->id?>" <?php if(!empty($v->type_id)){if($v1->id==$v->type_id) {echo "selected";}} ?>><?php echo $v1->type_name?></option>
										<?php endforeach;?>
									</select>
								</td>
								<td><input type="text" class="required" size='10' name="pgj[]" value="<?php echo $v->pgj?>"></td>
								<td>
									<input type='hidden' name='detail_id[]' value="<?php echo $v->id?>">
									<button name='del_btn' style='margin-left: 10px;height: 40px;' type='button' class='am-btn am-btn-default'>删除</button>
								</td>
							</tr>
						<?php endforeach;?>
					<?php endif;?>
					</tbody>
				</table>
			</fieldset>
        </div>
        <div class="formBar">
    		<ul>
    			<li><div class="buttonActive"><div class="buttonContent"><button type="submit" class="icon-save">保存</button></div></div></li>
    			<li><div class="button"><div class="buttonContent"><button type="button" class="close icon-close">取消</button></div></div></li>
    		</ul>
        </div>
	</form>
	<select class="combox" id="select_type">
		<?php foreach($list_type as $v1):?>
			<option value="<?php echo $v1->id?>"><?php echo $v1->type_name?></option>
		<?php endforeach;?>
	</select>
</div>
<script type="text/javascript" src="/static/js/layer/layer.js"></script>
<script>
	$(function() {
		$("[name='pgj[]']").keyup(function () {
			$(this).val($(this).val().replace(/[^0-9]/g, ''));
		}).blur(function(){
			$(this).val($(this).val().replace(/[^0-9]/g, ''));
		}).bind("paste", function () {  //CTR+V事件处理
			$(this).val($(this).val().replace(/[^0-9]/g, ''));
		}).css("ime-mode", "disabled"); //CSS设置输入法不可用

		$("#file_list").find("button").on('click',function () {
		if($('#file_list').children('tr').length <= 1){
			layer.msg('需至少保留一条‘评估’信息');
		}
		else{
			$(this).parent().parent().remove();
		}
		})

		$("#add_pgj").click(function(){
			$("#file_list").find("button").off('click');
			$("[name='pgj[]']").off('keyup').off('blur').off('paste');

				divbox = '';
				select_html ="<td><select class='combox' name='type_id[]'>"
				select_html += $("#select_type").html();
				select_html+="</select></td>"

				divbox =" <tr class='unitBox'>" +select_html +
					"<td><input type='text' class='required' size='10' name='pgj[]' value=''></td>"
				divbox +="<td><input type='hidden' name='detail_id[]'>"
				divbox +="<button name='del_btn' style='margin-left: 10px;height: 40px;' type='button' class='am-btn am-btn-default'>删除</button></td></tr>"
				$('#file_list').append(divbox);
				//绑定事件
				$("#file_list").find("button").on('click',function () {
					if($('#file_list').children('tr').length <= 1){
						layer.msg('需至少保留一条‘评估’信息');
					}
					else{
						$(this).parent().parent().remove();
					}
				})

			$("[name='pgj[]']").keyup(function () {
				$(this).val($(this).val().replace(/[^0-9]/g, ''));
			}).blur(function(){
				$(this).val($(this).val().replace(/[^0-9]/g, ''));
			}).bind("paste", function () {  //CTR+V事件处理
				$(this).val($(this).val().replace(/[^0-9]/g, ''));
			}).css("ime-mode", "disabled"); //CSS设置输入法不可用
		})

	})

</script>

