<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RichMenu extends Model
{
    /**
     * 資料庫記錄
     */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'menu';

    protected $primaryKey = 'idx';

    protected $casts = [
        'selected' => 'boolean',
        'online_status' => 'boolean',
        'size' => 'json',
    ];

    protected $fillable = [
        'title',
        'chat_bar_text',
        'selected',
        'publish_status',
        'size',
        'image',
        'alias_name',
        'online_status',
        'rich_menu_id',
    ];

    public function areas()
    {
        return $this->hasMany(RichMenuArea::class, '_menu', 'idx');
    }

    public function richMenuGroups()
    {
        return $this->belongsToMany(RichMenuGroup::class, 'menu_group', '_menu', '_group');
    }

    public function getImageUrlAttribute()
    {
        return url(Storage::url($this->attributes['image']));
    }

    // 以下方法需要抽到 Service

    public static function getExistingRichMenu($selectedMenuIdx)
    {
        return self::where('idx', $selectedMenuIdx)
            ->where('online_status', 1)
            ->exists();
    }

    public static function getMenuDataById($id)
    {
        return self::select('idx', 'title', 'chat_bar_text', 'selected', 'size', 'image', 'alias_name', 'online_status')
            ->where('idx', $id)
            ->first();
    }

    public static function getMenuArea($menuId)
    {
        return DB::table('menu_area')
            ->select('bounds', 'action')
            ->where('_menu', $menuId)
            ->get();
    }

    public static function menuExists($selectedMenuIdx)
    {
        return self::where('idx', $selectedMenuIdx)->exists();
    }


    public static function updateRichMenuId($selectedMenuIdx, $richMenuId)
    {
        self::where('idx', $selectedMenuIdx)->update(['rich_menu_id' => $richMenuId]);
    }

    public static function getMenuById($idx)
    {
        return DB::table('menu')->where('idx', $idx)->first();
    }

    public static function removeRichMenuId($selectedMenuIdx)
    {
        return self::where('idx', $selectedMenuIdx)->update(['rich_menu_id' => null]);
    }

    public static function setActive($idx)
    {
        return self::where('idx', $idx)->update(['online_status' => 1]);
    }

    public static function setInactive($idx)
    {
        return self::where('idx', $idx)->update(['online_status' => 0]);
    }

    public static function getAllActiveMenuIds()
    {
        return self::where('online_status', 1)->pluck('idx')->toArray();
    }
}
