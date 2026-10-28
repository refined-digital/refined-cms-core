<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One block from ApiContent::forModel().
 */
class PageContentResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'template' => \Str::camel($this->template),
            'uuid' => \Str::uuid(),
            // resolved one by one: a resource collection casts scalar values to arrays
            'content' => collect((array) $this->content)
                ->map(fn ($item) => (new PageContentItemResource($item))->toArray($request))
        ];
    }
}
