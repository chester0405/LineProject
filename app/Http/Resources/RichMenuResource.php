<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RichMenuResource extends JsonResource
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
            'chatBarText' => $this->chat_bar_text,
            'selected' => $this->selected,
            'size' => $this->size,
            'image' => $this->image_url,
            'areas' => $this->areas->map(function ($area) {
                return [
                    'bounds' => $area->bounds,
                    'action' => $area->action,
                ];
            }),
            'AliasName' => $this->alias_name,
            'OnlineStatus' => $this->online_status,
            'publishStatus' => $this->publish_status,
            'updatedAt' => $this->updated_at,

        ];
    }
}
