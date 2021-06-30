@php
  $config = config('page-banners.fields.image');
  $width = 1920;
  $height = 600;

  $images = [];
  $banners = $page->data->banners ?? [];
  if (sizeof($banners)) {
    foreach ($banners as $banner) {
      if ($banner->image->content) {
        $img = asset(
          image()
            ->load($banner->image->content)
            ->width($width)
            ->height($height)
            ->string()
          );
        $images[] = $img;
      }
    }
  }
@endphp
{!!
  view()
    ->make('templates.includes.banners')
    ->with(compact('images'))
    ->with('controls', true)
    ->with('pager', true)
!!}
