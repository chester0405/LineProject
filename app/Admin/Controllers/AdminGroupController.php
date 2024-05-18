<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\RichMenuGroup;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AdminGroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'RichMenuGroup';
    protected $textAlignCenter = 'text-align: center';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RichMenuGroup());

        $grid->column('idx', __('Idx'))->style($this->textAlignCenter);

        $grid->column('title', __('Title'))->style($this->textAlignCenter);

        $grid->column('is_default', __('IsDefault'))->display(function ($default) {
            if ($default) {
                return "<span class='label label-success'>預設</span>";
            }
        })->style($this->textAlignCenter);

        $grid->column('schedule_status', __('ScheduleStatus'))->display(function ($schedule) {
            if ($schedule) {
                return "<span class='label label-success'>TRUE</span>";
            } else {
                return "<span class='label label-danger'>FALSE</span>";
            }
        })->style($this->textAlignCenter);

        $grid->column('record_status', __('RecordStatus'))->display(function ($record) {
            if ($record) {
                return "<span class='label label-success'>ONLINE</span>";
            } else {
                return "<span class='label label-danger'>OFFLINE</span>";
            }
        })->style($this->textAlignCenter);

        $grid->column('release_at', __('ReleaseAt'))->style($this->textAlignCenter);

        $grid->column('removal_at', __('RemovalAt'))->style($this->textAlignCenter);

        //$grid->column('deleted_at', __('DeletedAt'))

        $grid->column('created_at', 'CreatedAt')->display(function ($created_at) {
            return Carbon::parse($created_at)->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        })->style($this->textAlignCenter);

        $grid->column('updated_at', 'UpdatedAt')->display(function ($created_at) {
            return Carbon::parse($created_at)->setTimezone('Asia/Taipei')->format('Y-m-d H:i:s');
        })->style($this->textAlignCenter);

        $grid->column('richMenus', 'Rich Menus')->display(function ($menus) {
            $html = '';
            if ($menus) {
                foreach ($menus as $menu) {
                    $html .= "{$menu['idx']}: {$menu['title']}<br>";
                }
            }

            return $html;
        });




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
        $show = new Show(RichMenuGroup::findOrFail($id));

        $show->field('idx', __('Idx'));
        $show->field('title', __('Title'));
        $show->field('is_default', __('Is default'));
        $show->field('schedule_status', __('Schedule status'));
        $show->field('release_at', __('Release at'));
        $show->field('removal_at', __('Removal at'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('richMenus', 'Rich Menus')->as(function ($menus) {
            return collect($menus)->pluck('idx', 'title')->toArray();
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
        $form = new Form(new RichMenuGroup());

        $form->text('title', __('Title'));
        $form->text('is_default', __('Is default'));
        $form->switch('schedule_status', __('Schedule status'));
        $form->datetime('release_at', __('Release at'))->default(date('Y-m-d H:i:s'));
        $form->datetime('removal_at', __('Removal at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
