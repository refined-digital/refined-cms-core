{{-- the table filters set on the module's index table: $tableSettings->filters, each a name, label and options (value => label) --}}
@if (!empty($tableSettings->filters))
    <form method="get" action="{{ $routes->index }}" class="data-table__filters">
        @foreach (request()->only(['keywords', 'perPage', 'sort', 'dir']) as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        @foreach ($tableSettings->filters as $filter)
            <label class="data-table__filter">
                <span>{{ $filter->label ?? ucfirst($filter->name) }}</span>
                <select name="{{ $filter->name }}" class="form__control" @change="$event.target.form.submit()">
                    <option value="">All</option>
                    @foreach ($filter->options as $value => $label)
                        <option value="{{ $value }}"{{ (string) request()->get($filter->name) === (string) $value ? ' selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
    </form>
@endif
