<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class PageMetaResource extends JsonResource
{

    public function toArray($request): array
    {
        return [
            'uri' => $this->uri,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'template' => $this->template->source,
        ];
    }
}