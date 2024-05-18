<?php

namespace App\Http\Controllers;

use App\Models\RichMenuGroup;
use App\Services\RichMenuService;
use App\Services\RichMenuGroupService;
use App\Http\Resources\RichMenuGroupResource;
use App\Http\Requests\RichMenuGroupStoreRequest;
use App\Http\Requests\RichMenuGroupSearchRequest;
use App\Http\Requests\RichMenuGroupUpdateRequest;
use Request;

class RichMenuGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(
        private RichMenuGroupService $richMenuGroupService, private RichMenuService $richMenuService
    )
    {
    }

    public function index(RichMenuGroupSearchRequest $request)
    {
        $searchResults = $this->richMenuGroupService->searchGroup($request->validated());

        $countGroup = RichMenuGroup::all()
            ->groupBy('schedule_status')
            ->map(fn ($item) => $item->count())->toArray();

        return response()->json([
            'data' => RichMenuGroupResource::collection($searchResults['data']),
            'totalSearch' => $searchResults['totalCount'],
            'totalGroupsSchedule' => $countGroup[1], // 1: 上架中
            'totalGroupsNonSchedule' => $countGroup[0], // 0: 下架中
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RichMenuGroupStoreRequest $request)
    {
        try {
            return new RichMenuGroupResource(
                $this->richMenuGroupService->createRichMenuGroup($request->validated())
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RichMenuGroup  $richMenuGroup
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, RichMenuGroup $richMenuGroup)
    {
        return new RichMenuGroupResource($richMenuGroup);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RichMenuGroup  $richMenuGroup
     * @return \Illuminate\Http\Response
     */
    public function update(RichMenuGroupUpdateRequest $request, RichMenuGroup $richMenuGroup)
    {
        return new RichMenuGroupResource(
            $this->richMenuGroupService->updateRichMenuGroup($richMenuGroup->idx, $request->validated())
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RichMenuGroup  $richMenuGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $idx)
    {
        try {
            $this->richMenuGroupService->deleteRichMenuGroup($idx);
        } catch (\Exception $e) {
            // 根據需要處理的異常，返回錯誤消息
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->noContent();
    }
}
