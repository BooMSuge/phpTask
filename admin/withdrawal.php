<?php
include 'head.php';
?>
<style>
    .demo-login-container {
        width: 320px;
        margin: 21px auto 0;
    }

    .demo-login-other .layui-icon {
        position: relative;
        display: inline-block;
        margin: 0 2px;
        top: 2px;
        font-size: 26px;
    }
</style>
<div class="layui-body">
    <div style="padding: 15px;">
        <!-- 注册表单开始 -->
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
            <legend>提现记录</legend>
        </fieldset>
        <!-- 内容主体区域 -->
        <div class="layui-col-xs12">
            <form class="layui-form layui-row layui-col-space16">
                <div class="layui-col-sm3">
                    <label class="layui-form-label">用户名:</label>
                    <div class="layui-input-block">
                        <input name="username" class="layui-input">
                    </div>
                </div>
                <div class="layui-col-sm3">
                    <label class="layui-form-label">服务状态:</label>
                    <div class="layui-input-block">
                        <select name="status">
                            <option value=""></option>
                            <option value="confirmed">已完成</option>
                            <option value="pending">待处理</option>
                            <option value="rejected">拒绝</option>
                        </select>
                    </div>
                </div>
                <div class="layui-btn-container layui-col-xs12">
                    <button type="button" style="float: left;" class="layui-btn layui-btn-primary layui-border" lay-submit lay-filter="doSearch">
                        <i class="layui-icon layui-icon-search"></i> 查询
                    </button>
                </div>
            </form>
        </div>
        <div style="padding: 6px;">
            <?php if ($_SESSION['user_type'] == 'admin') { ?> 
        <button id="batchDelete" class="layui-btn layui-btn-danger">批量删除</button>
         <?php } ?>
            <table class="layui-hide" id="test" lay-filter="test"></table>
        </div>
    </div>
<div id="dropdownButton" class="layui-hide">
    <div class="layui-clear-space">
        <a class="layui-btn layui-btn-xs" lay-event="confirm">确认</a>
        <a class="layui-btn layui-btn-xs" lay-event="reject">拒绝</a>
        <?php if ($_SESSION['user_type'] == 'admin') { ?> 
        <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
        <?php } ?>
    </div>
</div>

    <!-- 拒绝表单 -->
    <div id="rejectForm" style="display:none; padding:20px;">
        <form class="layui-form" action="">
            <input type="hidden" name="id" value="">
            <div class="layui-form-item">
                <label class="layui-form-label">拒绝备注:</label>
                <div class="layui-input-block">
                    <input type="text" name="notes" placeholder="请输入拒绝备注" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="rejectForm">提交</button>
                </div>
            </div>
        </form>
    </div>
    <table class="layui-hide" id="test" lay-filter="test"></table>
<script src="../layui/layui.js"></script>
<script>
layui.use(['table', 'form', 'layer'], function () {
    var $ = layui.$;
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;
    var isSubmitting = false; // 添加一个标志位来检查是否正在提交

    // 格式化当前时间的函数
    function getFormattedTime() {
        const now = new Date();
        return `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
    }

    // 更新状态的函数
    async function updateStatus(id, status) {
        if (isSubmitting) return; // 如果正在提交，则直接返回，避免重复提交
        isSubmitting = true;
        const notes = status === 'confirmed' ? `打款成功，到账时间: ${getFormattedTime()}` : '';

        try {
            let response = await fetch('../controller/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: status, notes: notes })
            });
            let result = await response.json();
            if (result.success) {
                layer.msg('操作成功', { icon: 1, time: 1000 });
                table.reload('test');
            } else {
                layer.msg(result.message || '操作失败', { icon: 2, time: 1000 });
            }
        } catch (error) {
            console.error('Error:', error);
            layer.msg('请求失败', { icon: 2, time: 1000 });
        }
        isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
    }

    // 批量删除的函数
    async function deleteSelected(ids) {
        if (isSubmitting) return; // 如果正在提交，则直接返回，避免重复提交
        isSubmitting = true;

        try {
            let response = await fetch('../controller/delete_withdrawals.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: ids })
            });
            let result = await response.json();
            if (result.success) {
                layer.msg('批量删除成功', { icon: 1, time: 1000 });
                table.reload('test');
            } else {
                layer.msg(result.message || '删除失败', { icon: 2, time: 1000 });
            }
        } catch (error) {
            console.error('Error:', error);
            layer.msg('请求失败', { icon: 2, time: 1000 });
        }
        isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
    }

    // 初始化表格
    function initTable(filters = {}) {
        table.render({
            elem: '#test',
            url: '../controller/get_withdrawals.php',
            where: filters, // 传递过滤参数
            defaultToolbar: ['filter', 'exports', 'print', {
                title: '提示',
                layEvent: 'LAYTABLE_TIPS',
                icon: 'layui-icon-tips'
            }],
            height: '650px',
            cellMinWidth: 60,
            totalRow: false,
            page: true,
            cols: [[
                { type: 'checkbox', fixed: 'left' }, // 添加选择框
                { field: 'id', fixed: 'left', width: 60, title: 'ID', sort: true },
                { field: 'username', width: 320, title: '用户名', sort: true },
                { field: 'amount', width: 200, title: '金额' },
                { field: 'notes', width: 630, title: '备注' },
                { field: 'image_url', width: 80, title: '收款码', templet: function(d) {
                    return '<img src="' + d.image_url + '" style="width: 50px; height: 50px;" onclick="showImage(this)">';
                }},
               { field: 'status', width: 160, title: '状态', templet: function(d) {
    return d.status === 'pending' ? '<span class="layui-badge layui-bg-blue">待处理</span>' :
           d.status === 'confirmed' ? '<span class="layui-badge layui-bg-green">已完成</span>' :
           d.status === 'rejected' ? '<span class="layui-badge layui-bg-red">拒绝</span>' : '未知状态';
}, sort: true },
{ fixed: 'right', title: '操作', width: 200, align: 'center', templet: function(d) {
    let buttons = '';
    if (d.status !== 'confirmed') {
        buttons += '<a class="layui-btn layui-btn-xs" lay-event="confirm">确认</a>';
        buttons += '<a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="reject">拒绝</a>';
    }
    <?php if ($_SESSION['user_type'] == 'admin') { ?> 
    buttons += '<a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>';
    <?php } ?>
    return buttons;
}}


            ]]
        });
    }

    // 初始化表格数据
    initTable();

    // 表单搜索事件处理
    form.on('submit(doSearch)', function (data) {
        initTable(data.field);
        return false; // 阻止表单跳转
    });

    // 监听行工具事件
    table.on('tool(test)', function (obj) {
        var data = obj.data;
        if (obj.event === 'confirm') {
            layer.confirm('确定要确认这笔提现吗？', function(index) {
                updateStatus(data.id, 'confirmed');
                layer.close(index);
            });
        } else if (obj.event === 'reject') {
            openRejectForm(data);
        } else if (obj.event === 'del') {
            layer.confirm('真的删除该记录吗？', function(index) {
                deleteRecord(data.id);
                layer.close(index);
            });
        }
    });

    // 图片点击放大
    window.showImage = function (img) {
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: ['600px', '400px'],
            skin: 'layui-layer-nobg', // 没有背景色
            shadeClose: true,
            content: '<img src="' + img.src + '" style="width: 100%; height: 100%;">'
        });
    };

    // 批量删除按钮事件
    $('#batchDelete').on('click', function() {
        var checkStatus = table.checkStatus('test');
        var data = checkStatus.data;
        var ids = data.map(function(item) {
            return item.id;
        });

        if (ids.length > 0) {
            layer.confirm('确定要删除选中的记录吗？', function(index) {
                deleteSelected(ids);
                layer.close(index);
            });
        } else {
            layer.msg('请选择要删除的记录', { icon: 2, time: 1000 });
        }
    });

    async function submitRejectForm(formdata, index) {
        if (isSubmitting) return; // 如果正在提交，则直接返回，避免重复提交
        isSubmitting = true;

        formdata.field.status = 'rejected';
        try {
            let response = await fetch('../controller/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formdata.field)
            });
            let result = await response.json();
            if (result.success) {
                layer.msg('操作成功', { icon: 1, time: 1000 }, function () {
                    layer.close(index);
                    table.reload('test');
                });
            } else {
                layer.msg(result.message || '操作失败', { icon: 2, time: 1000 });
            }
        } catch (error) {
            console.error('Error:', error);
            layer.msg('请求失败', { icon: 2, time: 1000 });
        }
        isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
    }

    async function deleteRecord(id) {
        if (isSubmitting) return; // 如果正在提交，则直接返回，避免重复提交
        isSubmitting = true;

        try {
            let response = await fetch('../controller/delete_withdrawal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            let result = await response.json();
            if (result.success) {
                layer.msg('删除成功', { icon: 1, time: 1000 });
                table.reload('test');
            } else {
                layer.msg(result.message || '删除失败', { icon: 2, time: 1000 });
            }
        } catch (error) {
            console.error('Error:', error);
            layer.msg('请求失败', { icon: 2, time: 1000 });
        }
        isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
    }

    function openRejectForm(data) {
        $("input[name='id']").val(data.id);
        $("input[name='notes']").val('');

        layer.open({
            type: 1,
            title: '拒绝备注',
            content: $('#rejectForm'),
            area: ['500px', '210px'],
            shade: 0,
            success: function(layero, index) {
                form.on('submit(rejectForm)', function (formdata) {
                    submitRejectForm(formdata, index);
                    return false;
                });
            }
        });
    }
});
</script>





<?php
include 'foot.php';
?>
