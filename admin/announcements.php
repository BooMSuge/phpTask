<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>公告管理</title>
    <link rel="stylesheet" href="../layui/css/layui.css">
    <script src="../layui/layui.js"></script>
</head>
<body>
<?php include 'head.php'; ?>
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
        <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
            <legend>公告管理</legend>
        </fieldset>
        <div class="layui-btn-container">
            <button class="layui-btn" id="addAnnouncement">添加公告</button>
        </div>
        <table class="layui-hide" id="announcementTable" lay-filter="announcementTable"></table>

        <!-- 添加公告表单 -->
        <div id="addAnnouncementForm" style="display: none; padding: 20px;">
            <form class="layui-form" lay-filter="addForm" action="">
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">内容</label>
                    <div class="layui-input-block">
                        <textarea name="content" required lay-verify="required" placeholder="请输入内容" class="layui-textarea"></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit lay-filter="saveAnnouncement">保存</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- 编辑公告表单 -->
        <div id="editAnnouncementForm" style="display: none; padding: 20px;">
            <form class="layui-form" lay-filter="editForm" action="">
                <input type="hidden" name="id">
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">内容</label>
                    <div class="layui-input-block">
                        <textarea name="content" required lay-verify="required" placeholder="请输入内容" class="layui-textarea"></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit lay-filter="updateAnnouncement">更新</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'foot.php'; ?>

<script type="text/html" id="actionButtons">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <?php if ($_SESSION['user_type'] == 'admin') { ?>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    <?php } ?>
</script>

<script>
layui.use(['table', 'form', 'layer'], function() {
    var table = layui.table;
    var form = layui.form;
    var layer = layui.layer;
    var isSubmitting = false; // 添加一个标志位来检查是否正在提交

    // 初始化表格
    table.render({
        elem: '#announcementTable',
        url: '../controller/announcements.php',
        method: 'GET',
        parseData: function(res) { // 将返回的数据格式化为符合table的规范
            return {
                "code": res.code, // 解析接口状态
                "msg": res.message, // 解析提示文本
                "count": res.data ? res.data.length : 0, // 解析数据长度
                "data": res.data // 解析数据列表
            };
        },
        cols: [[
            { field: 'id', title: 'ID', width: 80, sort: true },
            { field: 'content', title: '内容', width: 500 },
            { field: 'created_at', title: '创建时间', width: 180, sort: true },
            { field: 'updated_at', title: '更新时间', width: 180, sort: true },
            { title: '操作', width: 150, align: 'center', toolbar: '#actionButtons' }
        ]],
        page: true
    });

    // 添加公告按钮事件
    $('#addAnnouncement').on('click', function() {
        layer.open({
            type: 1,
            title: '添加公告',
            shade: 0,
            content: $('#addAnnouncementForm'),
            area: ['500px', '400px'],
            zIndex: layer.zIndex, // 增加zIndex属性
            success: function(layero) {
                layer.setTop(layero); // 使当前弹出层在最上面
            },
            end: function() {
                form.val('addForm', {content: ''}); // 重置表单
            }
        });
    });

    // 监听表单提交事件
    form.on('submit(saveAnnouncement)', function(data) {
        if (isSubmitting) return false; // 如果正在提交，则直接返回，避免重复提交
        isSubmitting = true;

        $.ajax({
            url: '../controller/announcements.php',
            type: 'POST',
            data: JSON.stringify(data.field),
            contentType: 'application/json',
            success: function(response) {
                isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
                if (response.code === 0) {
                    layer.msg('添加成功', { icon: 1 });
                    table.reload('announcementTable');
                    layer.closeAll('page');
                } else {
                    layer.msg('添加失败: ' + response.message, { icon: 2 });
                }
            },
            error: function() {
                isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
                layer.msg('添加失败，请稍后重试', { icon: 2 });
            }
        });
        return false;
    });

    // 更新公告表单提交事件
    form.on('submit(updateAnnouncement)', function(data) {
        if (isSubmitting) return false; // 如果正在提交，则直接返回，避免重复提交
        isSubmitting = true;

        $.ajax({
            url: '../controller/announcements.php',
            type: 'POST',
            data: JSON.stringify(data.field),
            contentType: 'application/json',
            success: function(response) {
                isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
                if (response.code === 0) {
                    layer.msg('更新成功', { icon: 1 });
                    table.reload('announcementTable');
                    layer.closeAll('page');
                } else {
                    layer.msg('更新失败: ' + response.message, { icon: 2 });
                }
            },
            error: function() {
                isSubmitting = false; // 无论成功与否，完成后都将提交标志位重置
                layer.msg('更新失败，请稍后重试', { icon: 2 });
            }
        });
        return false;
    });

    // 监听表格工具事件
    table.on('tool(announcementTable)', function(obj) {
        var data = obj.data;
        if (obj.event === 'edit') {
            form.val('editForm', data);
            layer.open({
                type: 1,
                shade: 0,
                title: '编辑公告',
                content: $('#editAnnouncementForm'),
                area: ['500px', '400px'],
                zIndex: layer.zIndex, // 增加zIndex属性
                success: function(layero) {
                    layer.setTop(layero); // 使当前弹出层在最上面
                }
            });
        } else if (obj.event === 'del') {
            layer.confirm('确定删除此公告？', function(index) {
                $.ajax({
                    url: '../controller/announcements.php',
                    type: 'DELETE',
                    data: JSON.stringify({ id: data.id }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.code === 0) {
                            layer.msg('删除成功', { icon: 1 });
                            table.reload('announcementTable');
                        } else {
                            layer.msg('删除失败: ' + response.message, { icon: 2 });
                        }
                    },
                    error: function() {
                        layer.msg('删除失败，请稍后重试', { icon: 2 });
                    }
                });
                layer.close(index);
            });
        }
    });
});
</script>
</body>
</html>
