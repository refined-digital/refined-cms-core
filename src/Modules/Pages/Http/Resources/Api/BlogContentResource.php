<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogContentResource extends JsonResource
{

    public function toArray($request): array
    {
        $date = $this->published_at->format('d/m/Y');
        $blocks = [
            [
                'template' => 'bannersInternal',
                'uuid' => \Str::uuid(),
                'content' => [
                    'images' => [PageContentItemResource::collection([
                        'uuid' => (object) ['content' => \Str::uuid(), 'type' => 3],
                        'link' => (object) ['content' => '', 'type' => 3],
                        'link2' => (object) ['content' => '', 'type' => 3],
                        'heading' => (object) ['content' => $this->name, 'type' => 3],
                        'content' => (object) ['content' => $date, 'type' => 3],
                        'link_title' => (object) ['content' => '', 'type' => 3],
                        'link_title2' => (object) ['content' => '', 'type' => 3],
                        'show_overlay' => (object) ['content' => true, 'type' => 3],
                        'image' => (object) [
                            'content' => $this->banner ? (object) ['id' => $this->banner, 'width' => 1920, 'height' => 535 ] : null,
                            'type' => 4
                        ]
                    ])]
                ]
            ],
            [
                'template' => 'content',
                'uuid' => \Str::uuid(),
                'content' => PageContentItemResource::collection([
                    // 'heading' => (object) ['content' => $this->name, 'type' => 3],
                    // 'sub_heading' => (object) ['content' => $date ?? false, 'type' => 3],
                    'content' => (object) ['content' => $this->content, 'type' => 3],
                ])
            ]
        ];

        return $blocks;
    }
}