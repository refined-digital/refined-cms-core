<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PageDataResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'meta' => new PageMetaResource($this->meta),
            'content' => PageContentResource::collection(ApiContent::forModel($this->resource)),
        ];
    }
}
