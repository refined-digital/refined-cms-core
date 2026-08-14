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

---

## Video

Uploading a video (`.mp4`) synchronously generates a compressed `-web.mp4`
derivative and a `-poster.webp` first-frame poster beside the untouched original.
The original is never modified.

### Requirement: ffmpeg and ffprobe

Both binaries must be on the server, e.g. `apt install ffmpeg` on Ubuntu (this
also provides `ffprobe`). Without them, uploads still succeed and videos still
serve — from the untouched original — but no derivatives are generated. The
feature is inert, not broken, when the binaries are missing.

Binary paths can be overridden with `FFMPEG_PATH` / `FFPROBE_PATH` if they're
not on `$PATH`.

### Encoding runs synchronously, in the upload request

This package has no queue infrastructure, so encoding happens inline while the
admin's upload request is held open. A large upload can hold that request open
for a while. The practical limits are **nginx's `proxy_read_timeout`** and
**PHP-FPM's `request_terminate_timeout`** — `set_time_limit(0)` is called
internally, but it has no effect on PHP-FPM's terminate timeout. A site that
expects large video uploads should raise both.

### Rendering: `video()->load($id)->banner()`

```php
video()->load($media->id)->banner();
```

Emits a `<video>` element pointing at the `-web.mp4` derivative, with the
`-poster.webp` as its `poster` attribute when one exists. If no derivative
exists — ffmpeg unavailable, encoding disabled, or not yet reprocessed — it
falls back to the untouched original, so the page never breaks for lack of a
derivative.

### `php artisan refinedCMS:reprocess-videos {id?}`

Regenerates derivatives for one media id, or for every video when the id is
omitted:

```bash
php artisan refinedCMS:reprocess-videos       # every video
php artisan refinedCMS:reprocess-videos 67    # a single media id
```

**Derivatives are generated at upload time and by this command, and by
nothing else.** A page load never generates or regenerates a derivative —
this is deliberate, so rendering a video stays cheap. That also means
deleting a derivative from disk is safe but not self-healing: the video keeps
serving its untouched original until this command is run again.

The command also matches more broadly than the upload hook: the upload hook
only encodes what `Media` types as a video, which today is `.mp4` only, while
this command matches any `video/*` mime type. So it will pick up containers
the upload hook skips — `.mov`, `.webm`, etc. — and encode them to `.mp4`.
This is intentional: it lets the command repair what the upload hook could
not handle, not a bug to work around.

### Config (`config/pages.php` → `video`)

| Key | Default | Meaning |
|-----|---------|---------|
| `encode` | `true` | Set `false` to disable video processing entirely (uploads still succeed, derivatives are never generated) |
| `crf` | `32` | h264 constant rate factor — the quality/size tradeoff. Lower is higher quality and larger |
| `preset` | `medium` | ffmpeg encoding preset — the speed/size tradeoff. Slower presets shrink the file a little further, at the cost of holding the upload request open longer |
| `maxWidth` | `1920` | Encoded and poster output are scaled down to this width when the source is wider |
| `poster` | `true` | Set `false` to skip poster generation |
| `posterQuality` | `80` | webp quality for the poster |
| `skipUnder` | `1500000` | Bitrate in bits per second, as `ffprobe` reports it. A source already at or under this, and within `maxWidth`, is served as-is rather than re-encoded |
| `ffmpeg` / `ffprobe` | `env('FFMPEG_PATH', 'ffmpeg')` / `env('FFPROBE_PATH', 'ffprobe')` | Binary paths or names |

There is no `video.disk` key — video storage always follows `pages.image.disk`.
