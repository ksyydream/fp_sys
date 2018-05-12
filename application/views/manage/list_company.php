<form id="pagerForm" method="post" action="<?php echo site_url('manage/list_company')?>">
    <input type="hidden" name="pageNum" value="<?php echo $pageNum;?>" />
    <input type="hidden" name="numPerPage" value="<?php echo $numPerPage;?>" />
    <input type="hidden" name="company" value="<?php echo $company;?>" />
    <input type="hidden" name="flag" value="<?php echo $flag;?>" />
    <input type="hidden" name="power_id" value="<?php echo $menuid;?>" />
    <input type="hidden" name="orderField" value="<?php echo $this->input->post('orderField');?>" />
    <input type="hidden" name="orderDirection" value="<?php echo $this->input->post('orderDirection');?>" />
</form>
<?php if($this->session->userdata('permission_id') == 1): ?>
<div class="pageHeader">
    <form onsubmit="return navTabSearch(this);" action="<?php site_url('manage/list_company')?>" method="post">
        <div class="searchBar">
            <table class="searchContent" id="search_purchase_order">
                <tr>
                    <td><label>公司：</label><input type="text" size="16" name="company" value="<?php echo $company;?>" /></td>
                    <td><label>状态：</label>
                        <select class="combox" name="flag">
                            <option value="">全部</option>
                            <option value="1" <?php if($flag==1){echo 'selected';}?>>启用</option>
                            <option value="2" <?php if($flag==2){echo 'selected';}?>>停用</option>
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
<?php endif ?>
<div class="pageContent">
    <div class="panelBar">
        <ul class="toolBar">
            <?php if($this->session->userdata('permission_id') == 1): ?>
                <li><a class="add" href="<?php echo site_url('manage/add_company')?>" target="dialog" rel="add_company" title="新建"><span>新建</span></a></li>
                <li><a class="delete" href="<?php echo site_url('manage/delete_company')?>/{id}" target="ajaxTodo"  title="确定要删除？" warn="请选择一条记录"><span>删除</span></a></li>
            <?php endif ?>
            <li><a class="edit" href="<?php echo site_url('manage/edit_company/{id}')?>" target="dialog" rel="edit_company" warn="请选择一条记录" title="查看"><span>查看</span></a></li>
        </ul>
    </div>

    <div layoutH="113" id="list_warehouse_in_print">
        <table class="list" width="100%" targetType="navTab" asc="asc" desc="desc">
            <thead>
            <tr>
                <th width="30">ID</th>
                <th>名称</th>
                <th>地址</th>
                <th>缩写</th>
                <th width="90">电话</th>
                <th width="80">状态</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($res_list)):
                foreach ($res_list as $row):
                    ?>
                    <tr target="id" rel=<?php echo $row->id; ?>>
                        <td><?php echo $row->id;?></td>
                        <td><?php echo $row->name;?></td>
                        <td><?php echo $row->address;?></td>
                        <td><?php echo $row->sx;?></td>
                        <td><?php echo $row->tel;?></td>
                        <td><?php
                            if($row->flag == 1){
                                echo '启用服务';
                            }else{
                                echo '暂停服务';
                            }
                            ?></td>
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