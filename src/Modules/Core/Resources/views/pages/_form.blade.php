@foreach($data->getFormFields() as $tab)
    <div class="tab__pane" v-show="tab == '{{ Str::slug($tab->name) }}'">
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

            @if (isset($tab->sections->bottom) && sizeof($tab->sections->bottom->blocks))
                <div class="tab__bottom">
                    @foreach($tab->sections->bottom->blocks as $group)
                        @include('core::form.blocks')
                    @endforeach
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
    window.app.tab = '{{ Str::slug($data->getFormFields()[0]->name) }}';
  @if (isset($data->type_id) || old('type_id'))
    @php
      $typeId = 1;
      if (isset($data->type_id)) {
          $typeId = $data->type_id;
      }
      if (old('type_id')) {
          $typeId = old('type_id');
      }
    @endphp
    window.app.form.typeId = {{ $typeId }};
  @endif

  var buttons = document.querySelectorAll('.app__content-header aside .button--blue');
  if (buttons.length) {
    var hasSaveButton = false;
    buttons.forEach(button => {
      if (button.innerHTML === 'Save') {
        hasSaveButton = true;
      }
    })

    if (hasSaveButton) {
      document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 's') {
          e.preventDefault();
          window.app.submitForm('save & new');
          return;
        }

        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
          e.preventDefault();
          window.app.submitForm('save');
        }
      })
    }
  }
</script>
@append
