<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

class SetupResource extends JsonResource
{
    public function __construct()
    {
    }

    public function toArray($request): array
    {
        $repo = new PageRepository();
        $mainMenu = $repo->getPagesForMenu(
            1,
            0,
            2
        );

        $footerMenu = $repo->getPagesForMenu(
            2,
            0,
            2
        );

        $settings = settings()->getKeyValue('pages');

        return [
            'menu' => [
                'main' => MenuResource::collection($mainMenu),
                'footer' => MenuResource::collection($footerMenu),
            ],
            'settings' => $settings
        ];
    }
}