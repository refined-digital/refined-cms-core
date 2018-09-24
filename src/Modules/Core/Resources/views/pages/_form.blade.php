@foreach($data->getFormFields() as $tab)
    <div class="tab__pane" v-show="tab == '{{ str_slug($tab->name) }}'">

        @if (isset($tab->sections))
            @if (isset($tab->sections->left))
                <div class="tab__groups">
                    <div class="tab__left">
                        @if (isset($tab->sections->left->blocks) && is_array($tab->sections->left->blocks))
                            @foreach($tab->sections->left->blocks as $group)
                                @include('core::form.blocks')
                            @endforeach
                        @endif
                    </div><!-- / tab left -->
                    <div class="tab__right">
                        @if (isset($tab->sections->right->blocks) && is_array($tab->sections->right->blocks))
                            @foreach($tab->sections->right->blocks as $group)
                                @include('core::form.blocks')
                            @endforeach
                        @endif
                    </div><!-- / tab right -->
                </div>
            @endif
        @elseif (isset($tab->blocks))
            @foreach($tab->blocks as $group)
                @include('core::form.blocks')
            @endforeach
        @elseif (isset($tab->fields))

            <div class="block">
                @if (sizeof($data->getFormFields()) > 1)
                    <header>
                        <h3>{{ $tab->name }}</h3>
                    </header>
                @endif

                @foreach($tab->fields as $groups)
                    @include('core::form.groups')
                @endforeach
            </div>

        @endif

    </div>
@endforeach

<input type="hidden" name="action" value="save" id="form--submit"/>

@section('scripts')
<script>
    window.app.tab = '{{ str_slug($data->getFormFields()[0]->name) }}'
</script>
@append