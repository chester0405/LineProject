<?php

namespace App\Http\Controllers;

use App\Http\Requests\RichMenuSearchRequest;
use App\Http\Requests\RichMenuStoreRequest;
use App\Http\Requests\RichMenuUpdateRequest;
use App\Http\Resources\RichMenuResource;
use App\Services\RichMenuService;
use Illuminate\Support\Facades\Storage;
use App\Models\RichMenu;
use Request;

class RichMenuController extends Controller
{
    /**
     * 初始化物件
     */
    public function __construct(private RichMenuService $richMenuService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *查看所有選單列表
     */
    public function index(RichMenuSearchRequest $request)
    {
        $searchResults = $this->richMenuService->searchMenu($request->validated());

        $groupCount = RichMenu::all()
            ->groupBy('publish_status')
            ->map(fn ($item) => $item->count());

        return response()->json([
            'data' => RichMenuResource::collection($searchResults['data']),
            'totalSearch' => $searchResults['totalCount'],
            'totalMenusNormal' => $groupCount['NORMAL']?? 0,
            'totalMenusDraft' => $groupCount['DRAFT']?? 0,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 新增圖文選單
     */
    public function store(RichMenuStoreRequest $request)
    {
        return new RichMenuResource($this->richMenuService->createRichMenu($request->validated()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idx
     * @return \Illuminate\Http\Response
     * 用ID尋找列表
     */
    public function show(Request $request, RichMenu $richMenu)
    {
        return new RichMenuResource($richMenu);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 用ID來更新圖文選單
     */
    public function update(RichMenuUpdateRequest $request, RichMenu $richMenu)
    {
        return new RichMenuResource(
            $this->richMenuService->updateRichMenu($richMenu->idx, $request->validated())
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     * 刪除圖文選單
     */
    public function destroy(int $idx)
    {
        try {
            $this->richMenuService->deleteRichMenu($idx);
        } catch (\Exception $e) {
            // 根據需要處理的異常，返回錯誤消息
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->noContent();
    }
}
