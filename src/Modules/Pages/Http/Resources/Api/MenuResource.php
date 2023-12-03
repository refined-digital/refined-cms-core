<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{

    public function toArray($request): array
    {
        $url = rtrim(str_replace(config('app.url'), '', $this->url), '/');

        if ($request->has('menu') && $request->get('menu') == 'single-level-url') {
            $slugs = explode('/', $url);
            $url = '/'.end($slugs);
        }

        if ($url === '') {
            $url = '/';
        }
        return [
            'id' => $this->id.':'.\Str::uuid(),
            'name' => $this->name,
            'url' => $url,
            'slug' => $this->slug,
            'children' => $this->children ? MenuResource::collection($this->children) : []
        ];
    }
}