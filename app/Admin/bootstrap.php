<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

Encore\Admin\Form::forget(['map', 'editor']);


// 這邊是照片處理
$imageModalHtml = <<<HTML
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <img id="largeImage" src="" style="width: 100%;">
            </div>
        </div>
    </div>
</div>
HTML;
Admin::html($imageModalHtml);

// 這邊是照片處理
$script = <<<SCRIPT
$(document).ready(function() {
    $('.view-image').click(function() {
        var image = $(this).data('image');
        $('#largeImage').attr('src', image);
        $('#imageModal').modal('show');
    });
});
SCRIPT;
Admin::script($script);

//這邊是menu_area處理
Admin::script('
    $(".view-areas").click(function() {
        var areasData = $(this).data("areas");
        var displayData = JSON.stringify(areasData, null, 4);
        $("#areasData").html(displayData);
        $("#areasModal").modal("show");
    });
');

$modalHtml = <<<HTML
        <div class="modal fade" id="areasModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Rich Menu Areas</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <pre id="areasData"></pre>
                    </div>
                </div>
            </div>
        </div>
        HTML;
Admin::html($modalHtml);

//PublishStatus的外框
Admin::css('
    .custom-label {
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
    }

    .label-success {
        background-color: #5cb85c; /* 綠色 */
        color: #fff; /* 白色文字 */
    }

    .label-danger {
        background-color: #d9534f; /* 紅色 */
        color: #fff; /* 白色文字 */
    }
');

//RichMenuId
$script = <<<SCRIPT
$(document).ready(function() {
    $('.show-rich-menu').click(function() {
        var richMenuId = $(this).data('rich-menu');
        
        // 創建一个框元素
        var modal = $('<div class="modal fade" tabindex="-1" role="dialog">');
        var modalDialog = $('<div class="modal-dialog" role="document">');
        var modalContent = $('<div class="modal-content">');
        var modalBody = $('<div class="modal-body">');
        
        // 插入RichMenuId的值到框中
        modalBody.text('RichMenuId: ' + richMenuId);
        
        // 将框元素组装起来
        modalContent.append(modalBody);
        modalDialog.append(modalContent);
        modal.append(modalDialog);
        
        // 追加框到頁面中
        $('body').append(modal);
        
        // 顯示框
        modal.modal('show');
    });
});
SCRIPT;
Admin::script($script);
