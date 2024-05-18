<?php

namespace App\Admin\Controllers;

use App\Models\RichMenu;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminMenuController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'RichMenu';
    protected $textAlignCenter = 'text-align: center';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RichMenu());

        $grid->filter(function ($filter) {
            $filter->column(1 / 2, function ($filter) {
                $filter->like('title', 'Title')->placeholder('Search Title...');
                // 添加其他过滤条件
            });
        });


        $grid->column('idx', __('Idx'))->style($this->textAlignCenter);

        $grid->column('title', __('Title'))->style($this->textAlignCenter);

        $grid->column('chat_bar_text', __('ChatBarText'))->style($this->textAlignCenter);

        $grid->column('selected', __('Selected'))->display(function ($selected) {
            if ($selected) {
                return "<span class='label label-success'>TRUE</span>";
            } else {
                return "<span class='label label-danger'>FALSE</span>";
            }
        })->style($this->textAlignCenter);

        $grid->column('publish_status', __('PublishStatus'))->display(function ($status) {
            $labelClass = ''; // 初始化標籤的 CSS 類

            if ($status === 'NORMAL') {
                $labelText = '一般';
                $labelClass = 'label-success'; // 使用紅色背景
            } elseif ($status === 'DRAFT') {
                $labelText = '草稿';
                $labelClass = 'label-danger'; // 使用綠色背景
            }

            // 返回包含顏色和文字的標籤
            return "<span class='label $labelClass custom-label'>$labelText</span>";
        })->style($this->textAlignCenter);

        $grid->column('size', __('Size'))->display(function ($size) {
            $sizeData = $size;
            if (is_array($sizeData) && isset($sizeData['width']) && isset($sizeData['height'])) {
                $width = $sizeData['width'];
                $height = $sizeData['height'];

                return "{$width}x{$height}";
            }
        })->style($this->textAlignCenter);

        $grid->column('image', __('Image'))->display(function ($image) {
            $imageUrl = Storage::url($image);

            return "<img src='{$imageUrl}' style='max-height:40px;' class='view-image' data-image='{$imageUrl}' />";
        })->style($this->textAlignCenter);

        $grid->column('alias_name', __('AliasId'))->style($this->textAlignCenter);

        $grid->column('rich_menu_id', __('RichMenuId'))->display(function ($richMenuId) {
            if ($richMenuId) {
                return "<a href='javascript:void(0);' class='show-rich-menu' data-rich-menu='{$richMenuId}'>ONLINE</a>";
            }
        })->style($this->textAlignCenter);

        $grid->column('online_status', __('OnlineStatus'))->display(function ($online) {
            if ($online) {
                return "<span class='label label-success'>ONLINE</span>";
            } else {
                return "<span class='label label-danger'>OFFLINE</span>";
            }
        })->style($this->textAlignCenter);

        $grid->column('created_at', 'CreatedAt')->display(function ($created_at) {
            return Carbon::parse($created_at)->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        })->style($this->textAlignCenter);

        $grid->column('updated_at', 'UpdatedAt')->display(function ($created_at) {
            return Carbon::parse($created_at)->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        })->style($this->textAlignCenter);

        $grid->column('areas', 'MenuAreas')->display(function ($a) {
            return "<a href='javascript:void(0);' class='view-areas' data-areas='" . json_encode($a) . "'>
            <i class='fa fa-eye'></i></a>";
        })->style($this->textAlignCenter);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(RichMenu::findOrFail($id));

        $show->field('idx', __('Idx'));
        $show->field('title', __('Title'));
        $show->field('chat_bar_text', __('Chat bar text'));
        $show->field('selected', __('Selected'));
        $show->field('publish_status', __('Publish status'));
        $show->field('size', __('Size'));
        $show->field('image', __('Image'));
        $show->field('alias_name', __('Alias name'));
        $show->field('rich_menu_id', __('Rich menu id'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->areas('Rich Menu Areas', function ($areas) {
            $areas->setResource('/admin/rich-menu-areas');
            $areas->bounds();
            $areas->action();
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RichMenu());

        $form->number('idx', __('Idx'));
        $form->text('title', __('Title'));
        $form->text('chat_bar_text', __('Chat bar text'));
        $form->switch('selected', __('Selected'));
        $form->text('publish_status', __('Publish status'));
        $form->text('size', __('Size'));
        $form->image('image', __('Image'));
        $form->text('alias_name', __('Alias name'));
        $form->text('rich_menu_id', __('Rich menu id'));
        $form->hasMany('areas', 'Rich Menu Areas', function (Form\NestedForm $form) {
            $form->text('bounds', 'Bounds');
            $form->text('action', 'Action');
        });

        return $form;
    }
}
