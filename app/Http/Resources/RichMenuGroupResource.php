<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RichMenuGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'idx' => $this->idx,
            'title' => $this->title,
            'isDefault' => $this->is_default,
            'scheduleStatus' => $this->schedule_status,
            'recordStatus' => $this->record_status,
            'releaseAt' => $this->release_at,
            'removalAt' => $this->removal_at,
            'richMenus' => $this->richMenus->map(function ($item) {
                return [
                    'idx' => $item->idx,
                    'title' => $item->title,
                    'updatedAt' => $this->updated_at,
                    'image' => $item->image_url,
                    'areas' => $item->areas->map(function ($areas) {
                        return [
                            'action' => $areas->action,
                            'bounds' => $areas->bounds,
                        ];
                    }),
                ];
            }),
            'updatedAt' => $this->updated_at,
            'images' => $this->richMenus->pluck('image_url'),
        ];
    }
}
