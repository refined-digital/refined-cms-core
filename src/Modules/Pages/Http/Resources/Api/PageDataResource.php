<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PageDataResource extends JsonResource
{

    public function toArray($request): array
    {
        // help()->trace($this->content, true);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'meta' => new PageMetaResource($this->meta),
            'content' => PageContentResource::collection($this->content),
        ];
    }
}