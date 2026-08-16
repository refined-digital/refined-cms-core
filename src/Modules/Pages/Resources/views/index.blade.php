@extends('core::layouts.master')

@section('title', $heading)

@section('template')
  @php
    $content = app(\RefinedDigital\CMS\Modules\Core\Aggregates\ContentAggregate::class)->getForConfig();

    // the workspace covers the admin sidebar, so surface the pages module's
    // sub items (templates etc.) inside it, honouring the menu's level gate
    $moduleLinks = [];
    foreach (app(\RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate::class)->getMenuItems() as $menuItem) {
        if (($menuItem->name ?? null) !== 'Pages' || empty($menuItem->children)) {
            continue;
        }
        foreach ($menuItem->children as $child) {
            if ($child->route === 'pages') {
                continue;
            }
            if (isset($child->max_user_level_id) && auth()->user()->user_level_id > $child->max_user_level_id) {
                continue;
            }
            $moduleLinks[] = [
                'name' => $child->name,
                'url' => route('refined.'.$child->route.'.index'),
            ];
        }
    }
  @endphp
  <rd-pages
    site-url="{{ rtrim(config('app.url'), '/') }}"
    public-url="{{ rtrim(env('PUBLIC_URL') ?? config('app.url'), '/') }}"
    :config="{{ json_encode(pages()->getConfig()) }}"
    :modules="{{ json_encode(pages()->getModules()) }}"
    :content="{{ json_encode($content) }}"
    :module-links="{{ json_encode($moduleLinks) }}"
  ></rd-pages>
@stop
