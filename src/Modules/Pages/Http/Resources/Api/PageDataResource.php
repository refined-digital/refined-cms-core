<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use RefinedDigital\Blog\Module\Models\Blog;

class PageDataResource extends JsonResource
{

    public function toArray($request): array
    {

        $resource = $this->meta->uriable_type === Blog::class
            ? new BlogContentResource($this)
            : PageContentResource::collection($this->content);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'meta' => new PageMetaResource($this->meta),
            'content' => $resource,
        ];
    }
}