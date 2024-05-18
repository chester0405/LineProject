<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\RichMenu;
use App\Models\RichMenuGroup;
use Illuminate\Support\Facades\Log;

class LineService
{
    protected $now;

    public function __construct()
    {
        $this->now = now()->toDateTimeString();
    }


    /**
     * 群組下架
     *
     * @param  Collection $menus
     * @return void
     */
    public function processDown($menus)
    {
        /**
         * 要下架的 menu
         * 包含:
         * 1. 群組時間到了
         * 2. 群組中途更新了選單, 導致選單無法被關聯時, 需要進行下架
         */


        foreach ($menus as $menu) {
            
            $groupRemoveAt = $menu->group_removal_at;
            $groupRecordStatus = $menu->group_record_status;
            $groupIsDefault = $menu->group_is_default;

            $menuOnlineStatus = $menu->menu_online_status;

            /**
             * 如果不是預設群組，且 removal_at 小於等於現在時間，且 為上架狀態，則執行下架
             */
            if ($groupRecordStatus == 1) { // 架上
                if (
                    !$groupIsDefault &&
                    ($groupRemoveAt <= $this->now)
                ) {
                    $this->processMenuDown($menu);
                } elseif ($menuOnlineStatus == 1) { //! 判斷也是錯的
                    //! 這邊要重寫
                    $this->processNoRelationMenuDown($menu);
                }
            }
        }

        /**
         * 預設群組下架, 這個邏輯要再想一下
         */
        $defaultGroups = RichMenuGroup::getMenusInDefaultGroup();
       
        $defaultGroup = $defaultGroups->first();

        if ($defaultGroup !== null) {
            $defaultGroup->record_status = 0;
            $defaultGroup->save();
        }
        
    }


    /**
     * 群組上架
     *
     * @param  mixed $menus
     * @return void
     */
    public function processUp($menus)
    {
            foreach ($menus as $menu) {
            $groupReleaseAt = $menu->group_release_at;
            $groupIsDefault = $menu->group_is_default;
            $groupRemovalAt = $menu->group_removal_at;


            if (!$groupIsDefault && ($groupReleaseAt <= $this->now) && ($groupRemovalAt > $this->now)) {
                $this->processMenuUp($menu);
            }
        }
    }

    /**
     * 上傳圖文選單群組
     */
    public function uploadRichMenu($menuId)
    {
        $menu = RichMenu::getMenuDataById($menuId);

        $menuArea = RichMenu::getMenuArea($menuId);

        /**
         * 1. 上傳 Rich Menu 基本資訊並取得 Rich Menu ID
         * 2. 上傳圖片
         * 3. 上傳別名（Alias Name）
         * 4. 設定為預設群組
         */

        $richMenuId = $this->uploadMenuInfo($menu, $menuArea);
        $this->uploadMenuImage($menu->image, $richMenuId);
        $this->uploadMenuAlias($menu->alias_name, $richMenuId);

        //! 理論上需要判斷, 是不是 default, 少開一個欄位
        $this->setDefaultMenu($richMenuId);

        RichMenu::updateRichMenuId($menuId, $richMenuId);
    }

    /**
     * 上傳 Rich Menu 基本資訊
     *
     * @param  mixed $menu
     * @param  mixed $menuArea
     * @return string
     */
    private function uploadMenuInfo($menu, $menuArea): string
    {
        $response = $this->getClient()
            ->request(
                'POST',
                'richmenu',
                [
                    'headers' => [
                        'Authorization' => $this->getAuthorization(),
                    ],
                    'json' => [
                        'name' => $menu->title,
                        'chatBarText' => $menu->chat_bar_text,
                        'selected' => $menu->selected,
                        'size' => $menu->size,
                        'areas' => $menuArea->map(function ($area) {
                            return [
                                /**
                                 * 要重寫 用Model
                                 */
                                'bounds' => json_decode($area->bounds),
                                'action' => json_decode($area->action),
                            ];
                        })->toArray(),
                    ],
                ]
            );

        $response = json_decode($response->getBody(), true);


        $richMenuId = $response['richMenuId'];

        Log::info("Rich menu {$richMenuId} upload success.");

        return $richMenuId;
    }

    /**
     * 上傳圖片
     */
    private function uploadMenuImage($imagePath, $richMenuId)
    {
        //! 需要改成 Storage 取得 圖片
        $imagePathWithStorage = storage_path('app/public/' . $imagePath);

        $this->getClient('data')
            ->request(
                'POST',
                "richmenu/{$richMenuId}/content",
                [
                    'headers' => [
                        'Authorization' => $this->getAuthorization(),
                        'Content-Type' => 'image/png',
                    ],
                    'body' => file_get_contents($imagePathWithStorage),
                ]
            );
    }

    /**
     * 上傳別名（Alias Name）
     */
    private function uploadMenuAlias($aliasName, $richMenuId)
    {
        $this->getClient()
            ->request(
                'POST',
                'richmenu/alias',
                [
                    'headers' => [
                        'Authorization' => $this->getAuthorization(),
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'richMenuId' => $richMenuId,
                        'richMenuAliasId' => $aliasName,
                    ],
                ]
            );
    }

    /**
     * 設定為預設選單
     */
    private function setDefaultMenu($richMenuId)
    {
        $this->getClient()->request(
            'POST',
            "user/all/richmenu/{$richMenuId}",
            [
                'headers' => [
                    'Authorization' => $this->getAuthorization(),
                ],
            ]
        );
    }

    /**
     * 移除圖文選單群組
     */
    public function removeRichMenu($selectedMenuIdx)
    {
        $menu = RichMenu::getMenuById($selectedMenuIdx);
        
        // 刪除 Rich Menu
        $this->removeRichMenuById($menu->rich_menu_id, $selectedMenuIdx);

        // 刪除 Alias Name
        $this->removeAliasName($menu->alias_name);
    }

    /**
     * 移除RichMenuId
     */
    private function removeRichMenuById($richMenuId, $selectedMenuIdx)
    {
        if (!$richMenuId) {
            return;
        }

        $response = $this->getClient()->request('DELETE', "https://api.line.me/v2/bot/richmenu/{$richMenuId}", [
            'headers' => [
                'Authorization' => $this->getAuthorization(),
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            \Log::info("Rich menu {$richMenuId} remove success.");
        } else {
            \Log::info("Error removing rich menu: {$response->getStatusCode()}.\n");
        }
    }

    /**
     * 移除別名（Alias Name）
     */
    private function removeAliasName($aliasName)
    {
        if (!$aliasName) {
            return;
        }

        $response = $this->getClient()->request('DELETE', "https://api.line.me/v2/bot/richmenu/alias/{$aliasName}", [
            'headers' => [
                'Authorization' => $this->getAuthorization(),
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            \Log::info("Alias name {$aliasName} remove success.");
        } else {
            \Log::info("Error removing alias name: {$response->getStatusCode()}.\n");
        }

    }

    /**
     * 取得 Line 上的 Rich Menu 列表
     *
     * @return array
     */
    private function getRichMenuListFromLine(): array
    {
        $response = $this->getClient()
            ->request(
                'GET',
                'richmenu/list',
                [
                    'headers' => [
                        'Authorization' => $this->getAuthorization(),
                    ],
                ]
            );

        return json_decode($response->getBody(), true)['richmenus'];
    }

    /**
     * 取得 Line 上的 Alias 列表
     *
     * @return array
     */
    private function getAliasList(): array
    {
        $response = $this->getClient()
            ->request(
                'GET',
                'richmenu/alias/list',
                [
                    'headers' => [
                        'Authorization' => $this->getAuthorization(),
                    ],
                ]
            );

        $data = json_decode($response->getBody(), true);

        // 根據LINE API的回應格式提取alias列表。如果結構不同，這部分可能需要調整。
        return $data['aliases'] ?? [];
    }

    /**
     * 群組整個上架
     */
    public function processMenuUp($menuInfo)
    {
        $menuId = $menuInfo->_menu;
        
        $this->uploadRichMenu($menuId);
        
        RichMenuGroup::setActive($menuInfo->group_idx);
        RichMenu::setActive($menuId);
    }

    /**
     * 群組整個下架
     */
    public function processMenuDown($menuInfo)
    {
        $menuId = $menuInfo->menu_idx;

        $this->removeRichMenu($menuId);

        RichMenuGroup::setInactive($menuInfo->group_idx);
        RichMenu::setInactive($menuId);
        RichMenu::removeRichMenuId($menuId);
    }

    /**
     * 選單整個下架
     */
    public function processNoRelationMenuDown($menuInfo)
    {
        $selectedMenuIdx = $menuInfo->menu_idx;

        //! 這邊 不應該全部刪除, 應該判斷 是否有真的需要下架
        //! 要補上 line 端的狀態 Table
        foreach ($this->getRichMenuListFromLine() as $menu) {
            // $this->removeRichMenuById($this->getClient(), $menu['richMenuId'], null);
            $this->removeRichMenuById($menu['richMenuId'], $selectedMenuIdx);

        }

        //! 這邊 不應該全部刪除, 應該判斷 是否有真的需要下架
        //! 要補上 line 端的狀態 Table
        foreach ($this->getAliasList() as $alias) {
            $this->removeAliasName($alias['richMenuAliasId']);
        }

        $allActiveMenuIds = RichMenu::getAllActiveMenuIds();

        // 移除除了選擇的選單以外的所有選單
        foreach (array_diff($allActiveMenuIds, [$selectedMenuIdx]) as $idx) {
            RichMenu::setInactive($idx);
            RichMenu::removeRichMenuId($idx);
        }
    }

    public function processDefaultGroupUp()
    {
        /**
         * 預設群組上架
         * 條件, 如果目前沒有任何群組在上架中, 則預設群組上架
         */
        $defaultMenus = RichMenuGroup::getMenusInDefaultGroup();
        if (!$defaultMenus->isEmpty()) {
            $activeNonDefaultGroups = RichMenuGroup::getActiveNonDefaultGroups();

            if ($activeNonDefaultGroups->isEmpty()) {
                foreach ($defaultMenus as $defaultMenu) {
                    $menuId = $defaultMenu->_menu;
                    $this->processMenuUp($defaultMenu);
                }
            }
        }
    }

    /**
     * 取得 Client
     *
     * @param  string $type
     * @return Client
     */
    private function getClient(string $type = 'default'): Client
    {
        $baseUri = $type == 'data' ? 'https://api-data.line.me/v2/bot/' : 'https://api.line.me/v2/bot/';

        return new Client([
            'base_uri' => $baseUri,
        ]);
    }

    private function getAuthorization()
    {
        return env('LINE_BOT_CHANNEL_TOKEN');
    }
}
