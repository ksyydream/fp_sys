<form id="pagerForm" method="post" action="<?php echo site_url('manage/list_pg')?>">
    <input type="hidden" name="pageNum" value="<?php echo $pageNum;?>" />
    <input type="hidden" name="numPerPage" value="<?php echo $numPerPage;?>" />
    <input type="hidden" name="xiaoqu" value="<?php echo $xiaoqu;?>" />
    <input type="hidden" name="flag" value="<?php echo $flag;?>" />
    <input type="hidden" name="orderField" value="<?php echo $this->input->post('orderField');?>" />
    <input type="hidden" name="orderDirection" value="<?php echo $this->input->post('orderDirection');?>" />
</form>

<div class="pageHeader">
    <form onsubmit="return navTabSearch(this);" action="<?php site_url('manage/list_pg')?>" method="post">
        <div class="searchBar">
            <table class="searchContent" id="search_purchase_order">
                <tr>
                    <td><label>小区名称：</label><input type="text" size="16" name="xiaoqu" value="<?php echo $xiaoqu;?>" /></td>
                    <td><label>状态：</label>
                        <select class="combox" name="flag">
                            <option value="">全部</option>
                           <option value="1" <?php if($flag==1){echo 'selected';}?>>在用</option>
                            <option value="-1" <?php if($flag==-1){echo 'selected';}?>>禁用</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="subBar">
                <ul>
                   <!-- <li><div class="button"><div class="buttonContent"><button id="clear_search">清除查询</button></div></div></li>-->
                    <li><div class="buttonActive"><div class="buttonContent"><button type="submit">执行查询</button></div></div></li>
                </ul>
            </div>
        </div>
    </form>
</div>
<div class="pageContent">
    <div class="panelBar">
        <ul class="toolBar">
            <li><a class="add" href="<?php echo site_url('manage/add_pg')?>" target="navTab" rel="add_pg" title="新建"><span>新建</span></a></li>
            <li><a class="edit" href="<?php echo site_url('manage/edit_pg/{id}')?>" target="navTab" rel="edit_pg" warn="请选择一条记录" title="查看"><span>查看</span></a></li>
           <!-- <li><a class="delete" href="<?php /*echo site_url('manage/delete_pg')*/?>/{id}" target="ajaxTodo"  title="确定要操作？" warn="请选择一条记录"><span>使用/禁用</span></a></li>-->
        </ul>
    </div>

    <div layoutH="140" id="list_warehouse_in_print">
        <table class="list" width="100%" targetType="navTab" asc="asc" desc="desc">
            <thead>
            <tr>
                <th width="60">ID</th>
                <th>小区名称</th>
                <th>乡镇</th>
                <th>状态</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($res_list)):
                foreach ($res_list as $row):
                    ?>
                    <tr target="id" rel=<?php echo $row->id; ?>>
                        <td><?php echo $row->id;?></td>
                        <td><?php echo $row->xiaoqu;?></td>
                        <td><?php echo $row->area_name;?></td>
                        <td><?php
                            if($row->flag==1){
                                echo '在用';
                            }
                            if($row->flag==-1){
                                echo '禁用';
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
<script type="text/javascript" src="/static/js/yui-min.js"></script>
<script type="text/javascript">
    //$('.item-icon').poshytip();
    YUI({
        modules: {
            'trip-calendar': {
                fullpath: '/static/js/calendar.js',
                type    : 'js',
                requires: ['trip-calendar-css']
            },
            'trip-calendar-css': {
                fullpath: '/static/css/calendar.css',
                type    : 'css'
            }
        }
    }).use('trip-calendar', function(Y) {
        new Y.TripCalendar({
            // minDate         : new Date,     //最小时间限制
            triggerNode     : '#J_DepDate', //第一个触节点
            finalTriggerNode: '#J_EndDate',  //最后一个触发节点
            isHoliday:true,
            isDateInfo:false,
            count:1
        });
    });
</script>