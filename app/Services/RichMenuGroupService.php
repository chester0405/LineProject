<?php

namespace App\Services;

use App\Models\RichMenuGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RichMenuGroupService
{
    /**
     * 新增群組
     *
     * @param  array $attributes
     * @return RichMenuGroup
     */
    public function createRichMenuGroup(array $attributes)
    {
        $releaseAt = $attributes['release_at'] ?? null;
        $removalAt = $attributes['removal_at'] ?? null;
        if ($releaseAt && $removalAt) {
            $this->checkTime($releaseAt, $removalAt);
        }

        // 將新建的群組設置為非預設群組
        $attributes['is_default'] = false;
        $attributes['record_status'] = false;

        $richMenuGroup = RichMenuGroup::create($attributes);

        $richMenuIds = $attributes['richMenus'] ?? [];
        if (!empty($richMenuIds)) {
            $this->attachRichMenu($richMenuGroup, $richMenuIds);
        }

        $richMenuGroup->load('richMenus');

        return $richMenuGroup;
    }

    public function updateRichMenuGroup(int $idx, array $attributes)
    {
        $richMenuGroup = RichMenuGroup::findOrFail($idx);

        // 預設群組無法更新
        if ($richMenuGroup->is_default) {
            // 移除特定屬性，以防止它們被更新
            $attributes = Arr::except($attributes, ['title', 'schedule_status', 'release_at', 'removal_at']);
        }

        // 1. 檢查時間
        $releaseAt = $attributes['release_at'] ?? null;
        $removalAt = $attributes['removal_at'] ?? null;
        if ($releaseAt && $removalAt) {
            $this->checkTime($releaseAt, $removalAt, $idx);
        }

        // 2. 若為上架 且 非預設群組，則檢查上架時間和下架時間是否為空
        $isOnAndNotDefault = $richMenuGroup->record_status && !$richMenuGroup->is_default;
        $isTimeEmpty = !$releaseAt || !$removalAt;
        throw_if(
            $isOnAndNotDefault && $isTimeEmpty,
            \Exception::class,
            '正在上架的群組的上架和下架時間不能為空'
        );

        // 移除現有的 richMenu, 並重新綁定
        $richMenuGroup->richMenus()->detach();
        $richMenuGroup->update($attributes);

        $richMenuIds = $attributes['richMenus'] ?? [];
        if (!empty($richMenuIds)) {
            $this->attachRichMenu($richMenuGroup, $richMenuIds);
        }

        $richMenuGroup->touch();
        $richMenuGroup->refresh();

        return $richMenuGroup;
    }

    public function deleteRichMenuGroup(int $idx): void
    {
        $richMenuGroup = RichMenuGroup::findOrFail($idx);


        throw_if(
            $richMenuGroup->is_default,
            \Exception::class,
            '預設資料不能被刪除'
        );

        throw_if(
            $richMenuGroup->record_status,
            \Exception::class,
            '正在上架的群組不能被刪除'
        );

        DB::table('menu_group')
            ->where('_group', $idx)
            ->update(['deleted_at' => now()]);
        
            RichMenuGroup::setInactive($richMenuGroup->idx);
        
        $richMenuGroup->delete();

    }

    /**
     * 群組搜尋
     *
     * @param  array $parameters
     * @return array
     */
    public function searchGroup(array $parameters)
    {
        $keyword = data_get($parameters, 'keyword');
        $sort = data_get($parameters, 'sort', 'desc');
        $limit = data_get($parameters, 'limit');
        $offset = data_get($parameters, 'offset');
        $status = data_get($parameters, 'status');

        /**
         * 排序規則
         *  1. 預設群組置於頂部
         *  2. record_status = 1 的條目置於前面
         *  3. release_at 為空的條目置於前面
         */
        $query = RichMenuGroup::query()
            ->orderBy('is_default', 'desc')
            ->orderBy('record_status', 'desc')
            ->orderByRaw('CASE
                WHEN release_at IS NULL THEN 1
                ELSE 0
                END,
                CASE
                WHEN schedule_status = 1 THEN release_at
                WHEN schedule_status = 0 THEN updated_at
                END ' . $sort)
            ->when($keyword !== null, function ($query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%');
            })
            ->when($status !== null, function ($query) use ($status) {
                return $query->where('schedule_status', $status);
            })
            ->with('richMenus');

        $totalCount = $query->count();

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        $data = $query->get();

        // 返回總筆數和資料
        return ['totalCount' => $totalCount, 'data' => $data];
    }

    private function attachRichMenu(RichMenuGroup $richMenuGroup, array $richMenuIds): void
    {
        $richMenuGroup->richMenus()->attach($richMenuIds);
    }

    /**
     * 檢查上架時間和下架時間是否有重疊
     *
     * @param  mixed $releaseAt 上架時間
     * @param  mixed $removalAt 下架時間
     * @return void
     * @throws Exception
     */
    private function checkTime($releaseAt, $removalAt,  $groupId = null)
    {
        if ($releaseAt && $removalAt) {
            // 檢查上架時間和下架時間是否有重疊
            $overlapExists = RichMenuGroup::query()
                ->where('idx', '!=', $groupId)
                ->where(
                    fn ($query) =>$query
                        ->where(
                            fn ($innerQuery) => $query
                                ->where('release_at', '<', $removalAt)
                                ->where('removal_at', '>', $releaseAt)
                    )
                    ->orWhere(
                        fn ($query) => $query
                            ->whereBetween('release_at', [$releaseAt, $removalAt])
                            ->orWhereBetween('removal_at', [$releaseAt, $removalAt])
                    )
                    ->orWhere(
                        fn ($query) => $query
                            ->where('release_at', '<=', $releaseAt)
                            ->where('removal_at', '>=', $removalAt)
                    )
                )
                ->exists();
                
            throw_if(
                $overlapExists,
                \Exception::class,
                '您的排程時間已重複，故無法儲存'
            );
        }
    }
}
