<style type="text/css">
    .file-box{ position:relative;width:340px}
    .btn{ background-color:#FFF; border:1px solid #CDCDCD;height:21px; width:70px;}
    .file{ position:absolute; top:0; right:80px; height:24px; filter:alpha(opacity:0);opacity: 0;width:300px }
</style>
<div class="pageContent">
    <form id="save_form" method="post" enctype="multipart/form-data" action="<?php echo site_url('manage/save_user');?>" class="pageForm required-validate" onsubmit="return iframeCallback(this, dialogAjaxDone);">
        <div class="pageFormContent" layoutH="55">
            <fieldset>
                <legend>用户信息</legend>
                <dl>
                    <dt>姓名：</dt>
                    <dd>
                        <input type="hidden" name="id" value="<?php if(!empty($id)) echo $id;?>">
                        <input name="rel_name" type="text" class="required" value="<?php if(!empty($rel_name)) echo $rel_name;?>" />
                    </dd>
                </dl>
                <?php if(!empty($id)){?>
                <dl>
                    <dt>重置密码:</dt>
                    <dd>
                        <button onclick="passwordRset(<?php if(!empty($id)) echo $id;?>)" type="button" class="icon-save" id="passwordreset">初始化密码</button>
                    </dd>
                </dl>
                <?php }?>
                <dl>
                    <dt>电话：</dt>
                    <dd>
                        <input name="tel" type="text" class="required" value="<?php if(!empty($tel)) echo $tel;?>" />
                    </dd>
                </dl>
                <dl>
                    <dt>所属公司：</dt>
                    <dd>
                        <select name="company_id" class="combox" id="company_id" >
                            <?php
                            if (!empty($company_list)):
                                foreach ($company_list as $row):
                                    $selected = !empty($company_id) && $row->id == $company_id ? "selected" : "";
                                    ?>
                                    <option value="<?php echo $row->id; ?>" <?php echo $selected; ?>><?php echo $row->name; ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>职级：</dt>
                    <dd>
                        <select name="role_id" class="combox" id="selectRole">
                            <?php
                            if (!empty($role_list)):
                                foreach ($role_list as $row):
                                    $selected = !empty($role_id) && $row['id'] == $role_id ? "selected" : "";
                                    ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>><?php echo $row['name']; ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </dd>
                </dl>

                <dl>
                    <dt>状态：</dt>
                    <dd>
                        <select name="flag" class="combox" id="selectFlag">
                            <option value="1" <?php if(!empty($flag)){if($flag==1){echo 'selected';}}else{echo 'selected';}?>>在职</option>
                            <option value="2" <?php if(!empty($flag)){if($flag==2){echo 'selected';}}?>>离职</option>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>是否主管：</dt>
                    <dd>
                        <select name="is_manager" class="combox" id="selectmanager">
                            <option value="1" <?php if(!empty($is_manager)){if($is_manager==1){echo 'selected';}}else{echo 'selected';}?>>是</option>
                            <option value="-1" <?php if(!empty($is_manager)){if($is_manager==-1){echo 'selected';}}?>>否</option>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>头像：</dt>
                    <dd>
                        <div class="file-box">
                            <input type="hidden" name="old_img" value="<?php if(!empty($pic)) echo $pic;?>" />
                            <input type='text' id='textfield' class='txt' value="<?php if(!empty($pic)) echo $pic;?>" />
                            <input type='button' class='btn' value='浏览...' />
                            <input type="file" name="userfile" class="file" id="fileField"  onchange="document.getElementById('textfield').value=this.value" />
                        </div>
                    </dd>
                </dl>
                <dl class="nowrap">
                    <dt>头像预览：</dt>
                    <dd id="img" style="float: none"><?php if(!empty($pic)):?><img height="50px" width="50px" src="<?php echo base_url().'/uploadfiles/profile/'.$pic;?>" /><?php endif;?></dd>
                </dl>
            </fieldset>
            <?php if($this->session->userdata('permission_id')==1):?>
            <fieldset>
                <legend>职务</legend>
                        <?php
                        if (!empty($position_list)):
                            foreach ($position_list as $row):
                                $checked = '';
                                if(!empty($pids)){
                                    foreach ($pids as $id){
                                        if($row['id'] == $id['pid']){
                                            $checked = 'checked';
                                        }
                                    }
                                }
                                ?>

                                <label><input <?php echo $checked; ?> name="pid[]" type="checkbox" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></label>

                                <?php
                            endforeach;
                        endif;
                        ?>
            </fieldset>
            <?php endif;?>
        </div>
        <div class="formBar">
            <ul>
                <li><div class="buttonActive"><div class="buttonContent"><button type="button" id="save_btn" class="icon-save">保存</button></div></div></li>
                <li><div class="button"><div class="buttonContent"><button type="button" class="close icon-close">取消</button></div></div></li>
            </ul>
        </div>
    </form>
</div>
<script>
    $("#fileField").change(function(){
        var objUrl = getObjectURL(this.files[0]);
        if (objUrl) {
            html = '<img height="50px" width="50px" src="'+objUrl+'" />';
            $("#img").html(html) ;
        }
    }) ;

    $("#company_id").change(function(){

    })

    $("#save_btn").click(function(data){

        $("#save_form").submit();
    })
    function passwordRset(id){
        var r=confirm("是否要初始化密码?")
        if (r==true)
        {
            $.getJSON("<?php echo site_url('manage/password_reset'); ?>/"+id,function(data){
                if(data==1){
                    alert('初始化成功');
                }else{
                    alert('初始化失败');
                }
            })
        }
    }
    //建立一個可存取到該file的url
    function getObjectURL(file) {
        var url = null ;
        if (window.createObjectURL!=undefined) { // basic
            url = window.createObjectURL(file) ;
        } else if (window.URL!=undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file) ;
        } else if (window.webkitURL!=undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file) ;
        }
        return url ;
    }
</script>
