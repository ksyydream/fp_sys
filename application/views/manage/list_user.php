<form id="pagerForm" method="post" action="<?php echo site_url('manage/list_user')?>">
    <input type="hidden" name="pageNum" value="<?php echo $pageNum;?>" />
    <input type="hidden" name="numPerPage" value="<?php echo $numPerPage;?>" />
    <input type="hidden" name="tel" value="<?php echo $tel;?>" />
    <input type="hidden" name="rel_name" value="<?php echo $relname;?>" />
    <input type="hidden" name="flag" value="<?php echo $flag;?>" />
    <input type="hidden" name="is_manager" value="<?php echo $is_manager;?>" />
    <input type="hidden" name="position_id" value="<?php echo $positionid;?>" />
    <input type="hidden" name="role_id" value="<?php echo $roleid;?>" />
    <input type="hidden" name="company_id" value="<?php echo $companyid;?>" />
    <input type="hidden" name="orderField" value="<?php echo $this->input->post('orderField');?>" />
    <input type="hidden" name="orderDirection" value="<?php echo $this->input->post('orderDirection');?>" />
</form>
<div class="pageHeader">
    <form onsubmit="return navTabSearch(this);" action="<?php site_url('manage/list_user')?>" method="post">
        <div class="searchBar">
            <table class="searchContent" id="search_purchase_order">
                <tr>
                    <td><label>员工名称：</label><input type="text" size="16" name="rel_name" value="<?php echo $relname;?>" /></td>
                    <td><label>员工手机：</label><input type="text" size="16" name="tel" value="<?php echo $tel;?>" /></td>
                </tr>
                <tr>
                    <td><label>所属公司：</label>
                        <select name="company_id" class="combox"  id="sel_com">
                            <option value="">请选择公司</option>
                            <?php
                            if (!empty($company_list)):
                                foreach ($company_list as $row):
                                    $selected = !empty($companyid) && $row->id == $companyid ? "selected" : "";
                                    ?>
                                    <option value="<?php echo $row->id; ?>" <?php echo $selected; ?>><?php echo $row->name; ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>组别：</label>
                        <select name="role_id" class="combox">
                            <option value="">请选择组别</option>
                            <?php
                            if (!empty($role_list)):
                                foreach ($role_list as $row):
                                    $selected = !empty($roleid) && $row['id'] == $roleid ? "selected" : "";
                                    ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>><?php echo $row['name']; ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </td>
                    <td><label>职务：</label>
                        <select name="position_id" class="combox">
                            <option value="">请选择职务</option>
                            <?php
                            if (!empty($position_list)):
                                foreach ($position_list as $row):
                                    $selected = !empty($positionid) && $row['id'] == $positionid ? "selected" : "";
                                    ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>><?php echo $row['name']; ?></option>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </td>
                    <td><label>状态：</label>
                        <select class="combox" name="flag">
                            <option value="">请选择状态</option>
                            <option value="1" <?php if($flag == 1) echo 'selected="selected"'?>>在职</option>
                            <option value="2" <?php if($flag == 2) echo 'selected="selected"'?>>离职</option>
                        </select>
                    </td>
                    <td><label>是否主管：</label>
                        <select class="combox" name="is_manager">
                            <option value="">请选择</option>
                            <option value="1" <?php if($is_manager == 1) echo 'selected="selected"'?>>主管</option>
                            <option value="-1" <?php if($is_manager == -1) echo 'selected="selected"'?>>组员</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="subBar">
                <ul>
                    <li><div class="button"><div class="buttonContent"><button id="clear_search">清除查询</button></div></div></li>
                    <li><div class="buttonActive"><div class="buttonContent"><button type="submit">执行查询</button></div></div></li>
                </ul>
            </div>
        </div>
    </form>
</div>
<div class="pageContent">
    <div class="panelBar">
        <ul class="toolBar">
            <?php if($this->session->userdata('permission_id') < 5): ?>
                <li><a class="add" href="<?php echo site_url('manage/add_user')?>" target="dialog" width="600" height="370" rel="add_user" title="新建"><span>新建</span></a></li>
                <!--
                <li><a class="delete" href="<?php echo site_url('manage/delete_user')?>/{id}" target="ajaxTodo"  title="确定要删除？" warn="请选择一条记录"><span>删除</span></a></li>
                -->
            <?php endif ?>
            <li><a class="edit" href="<?php echo site_url('manage/edit_user/{id}')?>" target="dialog" rel="edit_user" warn="请选择一条记录" title="查看"><span>查看</span></a></li>
        </ul>
    </div>

    <div layoutH="167" id="list_warehouse_in_print">
        <table class="list" width="100%" targetType="navTab" asc="asc" desc="desc">
            <thead>
            <tr>
                <th width="30">ID</th>
                <th width="100">姓名</th>
                <th>电话</th>
                <th>所在公司</th>
                <th>职级</th>
                <th width="30">状态</th>
            </tr>
            </thead>
            <tbody>
            <?php
                if (!empty($res_list)):
                    foreach ($res_list as $row):
            ?>
                    <tr target="id" rel=<?php echo $row->id; ?>>
                        <td><?php echo $row->id;?></td>
                        <td><?php echo $row->rel_name;?></td>
                        <td><?php echo $row->tel;?></td>
                        <td><?php echo $row->company_name;?></td>
                        <td><?php echo $row->role_name;?></td>
                        <td><?php
                            if($row->flag == 1){
                                echo '在职';
                            }else{
                                echo '离职';
                            }?></td>
                    </tr>
            <?php
                    endforeach;
                endif;
            ?>
            </tbody>
        </table>
    </div>
    <div class="panelBar" >
        <div class="pages">
            <span>显示</span>
            <select name="numPerPage" class="combox" onchange="navTabPageBreak({numPerPage:this.value})">
                <option value="20" <?php echo $this->input->post('numPerPage')==20?'selected':''?>>20</option>
                <option value="50" <?php echo  $this->input->post('numPerPage')==50?'selected':''?>>50</option>
                <option value="100" <?php echo $this->input->post('numPerPage')==100?'selected':''?>>100</option>
                <option value="200" <?php echo $this->input->post('numPerPage')==200?'selected':''?>>200</option>
            </select>
            <span>条，共<?php  echo $countPage;?>条</span>
        </div>
        <div class="pagination" targetType="navTab" totalCount="<?php echo $countPage;?>" numPerPage="<?php echo $numPerPage;?>" pageNumShown="10" currentPage="<?php echo $pageNum;?>"></div>
    </div>
</div>
<script>
    //清除查询
    $('#clear_search',navTab.getCurrentPanel()).click(function(){
        $(".searchBar",navTab.getCurrentPanel()).find("input").each(function(){
            $(this).val("");
        });
        $(".searchBar",navTab.getCurrentPanel()).find("select option").each(function(index){
            if($(this).val() == "")
            {
                $(this).attr("selected","selected");
            }
        });
    });

</script>