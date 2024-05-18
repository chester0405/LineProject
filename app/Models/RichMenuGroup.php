<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class RichMenuGroup extends Model
{
    /**
     * 資料庫記錄
     */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'group';

    protected $primaryKey = 'idx';

    protected $casts = [
        'is_default' => 'boolean',
        'schedule_status' => 'boolean',
        'record_status' => 'boolean',
    ];

    protected $fillable = [
        'title',
        'is_default',
        'schedule_status',
        'record_status',
        'release_at',
        'removal_at',
    ];

    public function richMenus()
    {
        return $this
            ->belongsToMany(RichMenu::class, 'menu_group', '_group', '_menu');
    }


    // 以下方法需要抽到 service

    /**
     * 取得所有已排程的選單包含群組資訊
     *
     * @return Collection
     */
    public static function getScheduledMenusWithGroupInfo()
    {
        return DB::table('menu_group')
            ->join(
                'group',
                'menu_group._group',
                '=',
                'group.idx'
            )
            ->join(
                'menu',
                'menu_group._menu',
                '=',
                'menu.idx'
            )
            ->select(
                'menu_group._menu',
                'group.release_at as group_release_at',
                'group.removal_at as group_removal_at',
                'group.idx as group_idx',
                'group.record_status as group_record_status',
                'group.is_default as group_is_default',
                'menu.idx as menu_idx',
                'menu.online_status as menu_online_status',
                'menu.rich_menu_id as menu_rich_menu_id',
                'menu.alias_name as menu_alias_name',
            )
            ->where(
                'group.schedule_status',
                '=',
                1
            )
            ->get();
    }

    /**
     * 取得 屬於 預設群組的 Menu
     *
     * @return Collection
     */
    public static function getMenusInDefaultGroup(): Collection
    {
        return self::join('menu_group', 'group.idx', '=', 'menu_group._group')
            ->where('group.is_default', 1)
            ->select('group.idx as group_idx', 'menu_group._menu')
            ->get();
    }

    /**
     * 取得 非預設群組, 且為已上架狀態
     *
     * @return Collection
     */
    public static function getActiveNonDefaultGroups(): Collection
    {
        return self::where('record_status', 1)->where('is_default', 0)->get();
    }


    public static function groupsInactive()
    {
        return self::where('record_status', 1)->count() === 0;
    }

    public static function setActive($idx)
    {
        return self::where('idx', $idx)->update(['record_status' => 1]);
    }

    /**
     * 設定群組為非啟用狀態
     *
     * @param  mixed $idx
     * @return void
     */
    public static function setInactive($idx)
    {
        $group = self::find($idx);

        // 只有當群組不是預設群組時，才將 schedule_status 設為 0 (未排程)
        self::where('idx', $idx)->update(
            array_merge(
                [
                    'record_status' => 0,
                    'release_at' => null,
                    'removal_at' => null,
                ],
                !$group->is_default ? ['schedule_status' => 0] : []
            )
        );
        Log::info("Group setInactive idx: " . $idx);
    }
    
}
