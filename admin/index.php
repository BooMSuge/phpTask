<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理界面</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../layui/css/layui.css" rel="stylesheet">
    <script src="../layui/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <style>
        .custom-search .layui-input-inline {
            margin-right: 10px;
        }

        .custom-search .layui-btn {
            margin-right: 10px;
        }

        .custom-alert {
            margin-bottom: 10px;
        }

        .search-input {
            width: auto;
        }

        .search-button {
            width: 80px;
        }

        .layui-table-tool {
            position: relative;
            width: 100%;
            min-height: 44px;
            line-height: 0px;
            padding: 0px 10px;
            border-width: 0px;
            border-bottom-width: 1px;
        }
    </style>
</head>
<body>
<?php include 'head.php'; ?>
<div class="layui-body">
    <div style="padding: 15px;">
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
            <legend>用户申请</legend>
        </fieldset>
        <div class="layui-col-xs12">
            <form class="layui-form layui-row layui-col-space16">
                <div class="layui-col-sm2">
                    <label class="layui-form-label">用户名:</label>
                    <div class="layui-input-block">
                        <input name="username" class="layui-input">
                    </div>
                </div>
                <div class="layui-col-sm2">
                    <label class="layui-form-label">卡密:</label>
                    <div class="layui-input-block">
                        <input name="verification_code" class="layui-input">
                    </div>
                </div>
                <div class="layui-col-sm2">
                    <label class="layui-form-label">手机号:</label>
                    <div class="layui-input-block">
                        <input name="phone" class="layui-input">
                    </div>
                </div>
                <div class="layui-col-sm2">
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
                <div class="layui-col-sm2">
                    <label class="layui-form-label">地区:</label>
                    <div class="layui-input-block">
                        <select name="region">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                <div class="layui-col-sm2">
                    <label class="layui-form-label">备注:</label>
                    <div class="layui-input-block">
                        <input name="notes" class="layui-input">
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
        <div id="dropdownButton" class="layui-hide">
            <div class="layui-clear-space">
                <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
                <?php if ($_SESSION['user_type'] == 'admin') { ?> 
                    <a class="layui-btn layui-btn-xs" lay-event="more">删除</a>
                <?php } ?>
            </div>
        </div>
        <div id="editForm" style="display:none; padding:20px;">
            <form class="layui-form" action="">
                <input type="hidden" name="id" value="">
                <div class="layui-form-item">
                    <label class="layui-form-label">卡密:</label>
                    <div class="layui-input-block">
                        <input type="text" name="verification_code" class="layui-input" readonly>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">手机号:</label>
                    <div class="layui-input-block">
                        <input type="text" name="phone" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">状态:</label>
                    <div class="layui-input-block">
                        <select name="status" lay-verify="required">
                            <option value="confirmed">已完成</option>
                            <option value="pending">待处理</option>
                            <option value="rejected">拒绝</option>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">备注:</label>
                    <div class="layui-input-block">
                        <input type="text" name="notes" placeholder="请输入备注" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">地区:</label>
                    <div class="layui-input-block">
                        <select name="region" lay-verify="required">
                            <option value="">请选择地区</option>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit lay-filter="editFormSubmit">立即提交</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../layui/layui.js"></script>
<script>
layui.use(['table', 'form', 'layer'], function () {
    var $ = layui.$;
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;
    var isSubmitting = false;
    var userType = "<?php echo $_SESSION['user_type']; ?>"; // 从PHP获取用户类型

    // 格式化当前时间的函数
    function getFormattedTime() {
        const now = new Date();
        return `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
    }

    // 更新状态的函数
    async function updateStatus(id, status) {
        if (isSubmitting) return;
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
        isSubmitting = false;
    }

    // 批量删除的函数
    async function deleteSelected(ids) {
        if (isSubmitting) return;
        isSubmitting = true;

        try {
            let response = await fetch('../controller/delete_claims.php', {
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
        isSubmitting = false;
    }

    // 加载地区选项
    function loadRegions() {
        $.ajax({
            url: '../controller/get_regions.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var regionSelect = $('select[name="region"]');
                    regionSelect.empty();
                    regionSelect.append(new Option('请选择地区', ''));
                    response.regions.forEach(function(region) {
                        regionSelect.append(new Option(region.name, region.name));
                    });
                    form.render('select');
                } else {
                    layer.msg('无法加载地区选项');
                }
            },
            error: function() {
                layer.msg('获取地区选项失败');
            }
        });
    }

    // 处理手机号只显示后四位
    function formatPhone(phone) {
        if (phone.length >= 4) {
            return '****' + phone.slice(-4);
        }
        return phone;
    }

    // 初始化表格
    function initTable(searchParams = {}) {
        table.render({
            elem: '#test',
            url: '../controller/form_list.php',
            where: searchParams,
            defaultToolbar: ['filter', 'exports', 'print', {
                title: '提示',
                layEvent: 'LAYTABLE_TIPS',
                icon: 'layui-icon-tips'
            }],
            height: '600px',
            cellMinWidth: 160,
            totalRow: false,
            page: true,
            limit: 13,
            css: ['.layui-table-tool-temp{padding-right: 125px;}'].join(''),
            cols: [[
                { type: 'checkbox', fixed: 'left' },
                { field: 'id', fixed: 'left', width: 80, title: 'ID', sort: true, hide: true },
                { fixed: 'left', field: 'username', width: 140, title: '用户名', sort: true },
                { field: 'verification_code', title: '卡密', width: 100 },
                { field: 'phone', title: '手机号', width: 150, templet: function(d) {
                    return userType === 'sub_admin' ? formatPhone(d.phone) : d.phone;
                }},
                { field: 'region', title: '地区', width: 150 },
                { field: 'created_at', title: '申请时间', width: 170, sort: true },
                { field: 'notes', title: '备注', width: 470 },
                {
                    field: 'status',
                    title: '订单状态',
                    width: 120,
                    sort: true,
                    templet: function(d) {
                        switch (d.status) {
                            case 'confirmed':
                                return '<span class="layui-badge layui-bg-green">已完成</span>';
                            case 'rejected':
                                return '<span class="layui-badge layui-bg-red">拒绝</span>';
                            case 'pending':
                                return '<span class="layui-badge layui-bg-blue">待处理</span>';
                            default:
                                return '<span class="layui-badge layui-bg-orange">未知状态</span>';
                        }
                    }
                },
                { fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#dropdownButton' }
            ]]
        });
    }

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

    // 表单搜索事件处理
    form.on('submit(doSearch)', function(data) {
        event.preventDefault();
        var loadingIndex = layer.load(1);
        initTable(data.field);
        layer.close(loadingIndex);
        return false;
    });

    // 初始化表格数据
    initTable();

    // 监听行工具事件
    table.on('tool(test)', function(obj) {
        var data = obj.data;

        if (obj.event === 'edit') {
            $.ajax({
                url: '../controller/get_list_update.php',
                type: 'GET',
                data: { id: data.id },
                success: function(response) {
                    var responseData = JSON.parse(response);
                    openEditForm(data, responseData);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else if (obj.event === 'more') {
            layer.confirm('真的删除 ' + data.username, function(index) {
                $.ajax({
                    url: '../controller/deleteBoss.php',
                    type: 'GET',
                    data: { id: data.id },
                    success: function(response) {
                        layer.msg('删除成功', { icon: 1, time: 800 });
                        obj.del();
                        layer.close(index);
                    },
                    error: function(xhr, status, error) {
                        layer.msg('删除失败: ' + error, { icon: 2 });
                    }
                });
            });
        }
    });

    // 打开编辑表单
    function openEditForm(data, responseData) {
        $("input[name='id']").val(responseData.id);
        $("input[name='username']").val(responseData.username);
        $("input[name='phone']").val(userType === 'sub_admin' ? formatPhone(responseData.phone) : responseData.phone);
        $("input[name='verification_code']").val(responseData.verification_code);
        $("select[name='status']").val(responseData.status);
        $("input[name='notes']").val(responseData.notes);
        $("select[name='region']").val(responseData.region);

        layer.open({
            type: 1,
            title: `编辑 - ${data.username}`,
            content: $('#editForm'),
            area: ['530px', '500px'],
            shade: 0,
            success: function(layero, index) {
                form.render('select');
                form.on('submit(editFormSubmit)', async function(formdata) {
                    let response = await fetch('../controller/update_date.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(formdata.field).toString()
                    });

                    if (response.ok) {
                        let result = await response.json();
                        if (result.success) {
                            layer.msg(result.message, { icon: 1, time: 1000 }, function() {
                                layer.close(index);
                                table.reload('test');
                            });
                        } else {
                            layer.msg('编辑失败: ' + result.message, { icon: 2, time: 1000 });
                        }
                    } else {
                        layer.msg('请求失败', { icon: 2, time: 1000 });
                    }
                    return false;
                });
            }
        });
    }

    // 初始加载地区选项
    loadRegions();
});
</script>
<?php include 'foot.php'; ?>
</body>
</html>
