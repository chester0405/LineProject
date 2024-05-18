<?php

namespace App\Services;

use App\Models\RichMenu;
use Illuminate\Support\Str;

class RichMenuService
{
    /**
     * 新增圖文選單
     */
    public function createRichMenu(array $attributes)
    {
        $attributes = array_merge($attributes, [
            'alias_name' => 'rich_menu_' . Strtolower(Str::random(20)),
            'online_status' => false,
        ]);

        $richMenu = RichMenu::create($attributes);



        if (isset($attributes['areas'])) {
            $richMenu->areas()->createMany($attributes['areas']);
        }

        return $richMenu;
    }

    public function updateRichMenu(int $idx, array $attributes)
    {
        $richMenu = RichMenu::findOrFail($idx);
        $richMenu->update($attributes);

        if (isset($attributes['areas'])) {
            $areas = $richMenu->areas()->get();
            foreach ($areas as $area) {
                $area->forceDelete();
            }

            $richMenu->areas()->createMany($attributes['areas']);
        }

        $richMenu->refresh();

        return $richMenu;
    }

    /**
     * 刪除圖文選單
     */
    public function deleteRichMenu(int $idx): void
    {
        $richMenu = RichMenu::findOrFail($idx);

        throw_if(
            $richMenu->richMenuGroups()->count() > 0,
            \Exception::class,
            "{$richMenu->title}選單，正在群組中，請從群組移除後再刪除"
        );

        $richMenu->areas()->delete();
        $richMenu->delete();
    }

    /**
     * 使用標題搜尋圖文選單例
     */
    public function searchMenu(array $parameters)
    {
        $keyword = data_get($parameters, 'keyword');
        $limit = data_get($parameters, 'limit');
        $offset = data_get($parameters, 'offset');
        $status = data_get($parameters, 'status');
        $sort = data_get($parameters, 'sort', 'desc');
        $orderBy = data_get($parameters, 'orderBy', 'updated_at');

        $query = RichMenu::query();

        // 條件搜索：當關鍵字不為 null 時應用模糊搜索。
        $query->when($keyword !== null, function ($query) use ($keyword) {
            return $query->where('title', 'like', '%' . $keyword . '%');
        });

        $query->when($status, function ($query, $status) {
            return $query->where('publish_status', $status);
        });

        $query->orderBy($orderBy, $sort);

        $totalCount = $query->count();

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        $data = $query->get();

        return [
            'totalCount' => $totalCount,
            'data' => $data,
        ];
    }
}
