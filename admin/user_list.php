<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>用户管理</title>
    <link rel="stylesheet" href="../layui/css/layui.css">
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
        .layui-table img {
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include 'head.php'; ?>
<div class="layui-body">
    <div style="padding: 15px;">
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
            <legend>用户列表</legend>
        </fieldset>
        <div class="layui-col-xs12">
            <form class="layui-form layui-row layui-col-space16">
                <div class="layui-col-sm3">
                    <label class="layui-form-label">用户名:</label>
                    <div class="layui-input-block">
                        <input name="username" class="layui-input">
                    </div>
                </div>
                <div class="layui-btn-container layui-col-xs12">
                    <button type="button" style="float: left;" class="layui-btn layui-btn-primary layui-border" lay-submit lay-filter="doSearch">
                        <i class="layui-icon layui-icon-search"></i> 查询
                    </button>
                </div>
            </form>
        </div>
        <div id="userList" class="layui-form"></div>
        <div id="editUserForm" style="display: none; padding: 20px;">
            <form class="layui-form" action="" lay-filter="editForm">
                <div class="demo-login-container">
                    <input type="hidden" name="id">
                    <div class="layui-form-item">
                        <div class="layui-input-wrap">
                            <div class="layui-input-prefix">
                                <i class="layui-icon layui-icon-username"></i>
                            </div>
                            <input type="text" name="username" lay-verify="required" placeholder="用户名" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-wrap">
                            <div class="layui-input-prefix">
                                <i class="layui-icon layui-icon-password"></i>
                            </div>
                            <input type="password" name="password" placeholder="密码（可选）" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-wrap">
                            <div class="layui-input-prefix">
                                <i class="layui-icon layui-icon-rmb"></i>
                            </div>
                            <input type="text" name="balance" placeholder="余额" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit lay-filter="editFormSubmit">立即提交</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php include 'foot.php'; ?>
    </div>
</div>
<script type="text/html" id="actionButtons">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <?php if ($_SESSION['user_type'] == 'admin') { ?>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    <?php } ?>
</script>
<script>
layui.use(['form', 'table', 'layer'], function() {
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;

    // 初始化表格
    function loadTableData(filters = {}) {
        $.getJSON('../controller/user_list.php', filters, function(data) {
            table.render({
                elem: '#userList',
                cols: [[
                    { field: 'id', title: 'ID', minWidth: 80 },
                    { field: 'username', title: '用户名', minWidth: 120 },
                    { field: 'balance', title: '余额', minWidth: 10 },
                    { field: 'image_url', title: '收款码', minWidth: 100, templet: d => `<div onclick="showImage('${d.image_url}')"><img src="${d.image_url}" style="height: 30px;"></div>`},
                    { field: 'created_at', title: '创建时间', sort: true, minWidth: 160 },
                    { title: '操作', align: 'center', minWidth: 160, toolbar: '#actionButtons' }
                ]],
                data: data,
                page: true,
                limit: 13,
                height: 'full-200',
                text: {
                    none: '没有找到相关数据'
                }
            });
        }).fail(function() {
            layer.msg('加载数据失败，请重试', {icon: 2});
        });
    }

    // 初始加载数据
    loadTableData();

    // 监听搜索操作
    form.on('submit(doSearch)', function(data) {
        var filters = data.field; // 获取表单集合的值
        loadTableData(filters); // 重新加载表格数据，根据过滤条件
        return false; // 阻止表单跳转
    });

    // 监听行工具事件
    table.on('tool(userList)', function(obj) {
        var data = obj.data; // 获取当前行的数据
        if (obj.event === 'del') {
            // 删除操作
            layer.confirm('确定删除此用户？', function(index) {
                $.post('../controller/delete_user.php', { id: data.id }, function(res) {
                    res = JSON.parse(res);
                    if (res.success) {
                        obj.del(); // 删除表格行
                        layer.close(index);
                        layer.msg('删除成功');
                    } else {
                        layer.msg('删除失败: ' + res.message);
                    }
                });
            });
        } else if (obj.event === 'edit') {
            // 编辑操作
            var editIndex = layer.open({
                type: 1,
                title: '编辑用户',
                content: $('#editUserForm').html(),
                success: function(layero, index) {
                    form.val("editForm", data);
                    form.on('submit(editFormSubmit)', function(editData) {
                        $.post('../controller/update_user.php', editData.field, function(res) {
                            try {
                                console.log("原始响应:", res); // 打印原始响应
                                if (res.success) {
                                    layer.close(editIndex);
                                    loadTableData(); // 更新数据
                                }
                                layer.msg(res.message);
                            } catch (e) {
                                console.error("解析JSON时出错:", e);
                                console.error("服务器响应内容:", res);
                                alert("服务器返回了无效的响应");
                            }
                        }, 'json') // 指定返回类型为 JSON
                        .fail(function(xhr, status, error) {
                            console.error("请求出错:", error);
                            alert("请求失败: " + error);
                        });
                        return false;
                    });
                }
            });
        }
    });
});

function showImage(src) {
    layer.open({
        type: 1,
        title: false,
        closeBtn: 0,
        area: ['auto'],
        skin: 'layui-layer-nobg', // 没有背景色
        shadeClose: true,
        content: `<img src="${src}" style="display: block; width: 100%; max-width: 600px; margin: 10px auto; height: auto;">`
    });
}
</script>
</body>
</html>
