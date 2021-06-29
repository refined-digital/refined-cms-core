@foreach ($page->content as $content)
  @if (view()->exists($content->template))
    {!! view()->make($content->template)->with(compact('page'))->with('content', $content->content) !!}
  @else
    <p style="color:#f00">Template "{{ $content->template }}" does not exist</p>
  @endif
@endforeach
