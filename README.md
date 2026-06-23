# Refined CMS

Laravel CMS core package (`refineddigital/cms`).

---

## Form Builder

Admin create/edit forms are defined on each model. Instead of hand-writing deeply
nested arrays, models expose a **fluent, Filament-style schema** built from typed
field and layout classes.

A model defines its form by implementing `formSchema()` and returning an array of
`Tab`s:

```php
use RefinedDigital\CMS\Modules\Core\Forms\Tab;
use RefinedDigital\CMS\Modules\Core\Forms\Block;
use RefinedDigital\CMS\Modules\Core\Forms\Row;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\TextInput;
use RefinedDigital\CMS\Modules\Core\Forms\Fields\Select;

class UserGroup extends CoreModel
{
    public function formSchema(): array
    {
        return [
            Tab::make('Content')->schema([
                Row::make([
                    Select::make('active', 'Active')->required()->options([1 => 'Yes', 0 => 'No']),
                    TextInput::make('name', 'Name')->required(),
                ]),
            ]),
        ];
    }
}
```

> The legacy `public $formFields = [...]` array (and the `formFields()` method form)
> still works — `formSchema()` simply takes precedence when present. Convert at your
> own pace, or use the converter command below.

### Layout

Forms nest **Tab → (Section | Block) → Row → Field**. You only use the layers you need.

| Class | Purpose | Factory |
|-------|---------|---------|
| `Tab` | A top-level tab in the editor | `Tab::make('Details')` |
| `Section` | A column within a tab (`left` / `right` / `bottom`) | `Section::left()`, `Section::right()`, `Section::bottom()` |
| `Block` | A titled card of fields | `Block::make('Profile')` |
| `Row` | Fields rendered side-by-side | `Row::make([...])` |

Each layout container takes its children via `->schema([...])` (except `Row`, which
takes its fields directly: `Row::make([...])`).

**Rows are how you put fields side-by-side.** Fields in the same `Row` share a line;
each `Row` is a new line. A bare field passed where a row is expected is placed on its
own row automatically.

#### A tab can hold one of three things

```php
// 1. left / right / bottom sections (for split layouts like Tags)
Tab::make('Content')->schema([
    Section::left()->schema([ Block::make('Content')->schema([...]) ]),
    Section::right()->schema([ Block::make('Image')->schema([...]) ]),
]),

// 2. blocks directly (titled cards stacked down the tab)
Tab::make('User Details')->schema([
    Block::make('Profile')->schema([...]),
    Block::make('Password')->schema([...]),
]),

// 3. rows / fields directly (a single implicit block)
Tab::make('Content')->schema([
    Row::make([ TextInput::make('name')->required() ]),
]),
```

> Don't mix sections, blocks, and rows at the same level inside one tab — pick one shape.

### Fields

All fields share a common fluent API and compile to the renderer's field definition.

| Class | Renders as |
|-------|-----------|
| `Field` | generic — set any type with `->type('...')` |
| `TextInput` | text input (plus `->email()`, `->url()`, `->number()`) |
| `Textarea` | textarea |
| `Select` | dropdown (`->options([...])`) |
| `RichEditor` | rich text editor |
| `Image` | image picker |
| `FileUpload` | file picker |
| `Password` | password input |

```php
TextInput::make('first_name', 'First Name')->required();
TextInput::make('email', 'Email')->email()->required()->note('Used for login');
Select::make('active', 'Active')->required()->options([1 => 'Yes', 0 => 'No']);
RichEditor::make('content');
Image::make('image')->hideLabel();
```

`make($name, $label = null)` — the second argument is the label. If omitted, a label
is derived from the field name (`first_name` → `First Name`).

#### Field methods

| Method | Effect |
|--------|--------|
| `->label(string)` | set the field label |
| `->type(string)` | set the field type (on the generic `Field`) — e.g. a custom `userLevels` type |
| `->required(bool = true)` | mark required |
| `->hideLabel(bool = true)` | render without a visible label |
| `->options(array)` | options for selects |
| `->note(string)` | help text shown below the field (HTML allowed) |
| `->preNote(string)` | help text shown above the field |
| `->attrs(array)` | extra HTML/Vue attributes, e.g. `['v-model' => 'content.name', '@keyup' => 'updateSlug']` |
| `->extra(string, mixed)` | set any other renderer key not covered above |

#### Custom field types

The renderer supports CMS-specific types (`userLevels`, `userGroups`, `tagType`, …)
that map to blade partials under `core::form.elements.*`. Use the generic `Field`
with `->type()`:

```php
Field::make('user_level_id', 'User Level')->type('userLevels')->required();
Field::make('groups', 'User Group')->type('userGroups');
```

### Full example (split layout with sections)

```php
public function formSchema(): array
{
    return [
        Tab::make('Content')->schema([
            Section::left()->schema([
                Block::make('Content')->schema([
                    Row::make([
                        TextInput::make('name', 'Name')->required(),
                        Field::make('type', 'Type')->type('tagType')->required(),
                    ]),
                    RichEditor::make('content', 'Content'),
                ]),
            ]),
            Section::right()->schema([
                Block::make('Image')->schema([
                    Image::make('image', 'Image')->hideLabel(),
                ]),
            ]),
        ]),
    ];
}
```

### Converting a legacy model

A console command rewrites a model's legacy `$formFields` array (or `formFields()`
method) into the fluent `formSchema()`:

```bash
# print the generated formSchema() + the imports it needs
php artisan refinedCMS:convert-form-schema "App\RefinedCMS\Blog\Models\Post"

# or write it straight into the model file
php artisan refinedCMS:convert-form-schema "App\RefinedCMS\Blog\Models\Post" --write
```

Pass the fully-qualified model class name. Without `--write` it prints the code for
you to paste; with `--write` it inserts the imports and replaces the legacy form
fields in place. Review the result and run your tests.

> New modules generated with `php artisan make:module` already scaffold a
> `formSchema()` using this builder.
