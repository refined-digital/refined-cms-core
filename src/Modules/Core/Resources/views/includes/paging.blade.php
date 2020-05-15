@if($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->total() > $data->perPage())
<div class="paging">
    {!! $data->appends(request()->except(['page', 'done']))->links() !!}
</div>
@endif
