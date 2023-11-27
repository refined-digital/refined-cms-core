<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PageContentResource extends JsonResource
{

    public function toArray($request): array
    {
        $template = \Str::camel(str_replace('templates.content.', '', $this->template));

        $content = (array) $this->content;

        return [
            'template' => $template,
            'uuid' => \Str::uuid(),
            'content' => PageContentItemResource::collection($content)
        ];
    }
}