<div class="block">
    @if(isset($group->name))
    <header>
        <h3>
          {{ $group->name }}
        </h3>
    </header>
    @endif

    @foreach($group->fields as $groups)
        @include('core::form.groups')
    @endforeach

</div>