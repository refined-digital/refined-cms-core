@if($data instanceof \Illuminate\Pagination\LengthAwarePaginator)
<div class="paging">
    {!! $data->appends(request()->except(['page', 'done']))->links() !!}
</div>
@endif