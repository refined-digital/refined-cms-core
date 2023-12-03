<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use RefinedDigital\CMS\Modules\Media\Models\Media;

class PageContentItemResource extends JsonResource
{

    public function toArray($request): array|string|\stdClass
    {

        $content = $this->content;
        $this->uuid = \Str::uuid();

        // is an image or file
        if (($this->type == 4 || $this->type == 5) && isset($this->content->id)) {
            $content = $this->content;
            $media = Media::find($this->content->id);
            if ($media) {
                unset($content->id);
                $content->url = $media->external_url ?? $media->link->original;
                $content->id = $media->external_id ?? $media->id;
                $content->uuid = \Str::uuid();
            }
        }

        // todo: fix this, should be using this same resource to do its thing
        if ($this->type == 9) {
            $content = [];
            foreach ($this->content as $tKey => $row) {
                $content[$tKey] = [
                    'uuid' => \Str::uuid(),
                ];
                foreach ($row as $key => $value) {
                    $c = $value->content;
                    // image or file
                    if (($value->type == 4 || $value->type == 5) && isset($value->content->id)) {
                        $media = Media::find($value->content->id);
                        if ($media) {
                            unset($value->id);
                            $c->url = $media->external_url ?? $media->link->original;
                            $c->id = $media->external_id ?? $media->id;
                        }
                    }

                    $content[$tKey][$key] = $c;
                }
            }
        }


        return $content;
    }
}