@php
  $images = [];
  foreach ($content->images as $img) {
      if (isset($img->image) && $img->image->id) {
        $image = new stdClass();
        $image->image = asset(
          image()
            ->load($img->image->id)
            ->width($img->image->width)
            ->height($img->image->height)
            ->string()
          );
        $image->alt = $img->image->alt ?? $img->image->fileAlt;
        $images[] = $image;
      }
  }
@endphp
{!!
  view()
    ->make('templates.includes.banners')
    ->with(compact($images))
    ->with('controls', true)
    ->with('pager', true)
!!}

