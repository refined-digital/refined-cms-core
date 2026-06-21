<?php

namespace RefinedDigital\CMS\Modules\Core\Forms\Fields;

use Illuminate\Support\Str;

/**
 * Base fluent form field. Typed fields (TextInput, Select, …) extend this.
 *
 * Every field compiles to the legacy associative-array shape consumed by the
 * form blades via toArray(), so the renderer is untouched.
 */
class Field
{
    protected ?string $type = null;
    protected bool $required = false;
    protected bool $hideLabel = false;
    protected ?array $options = null;
    protected ?string $note = null;
    protected ?string $preNote = null;
    protected array $extra = [];

    public function __construct(
        protected string $name,
        protected ?string $label = null,
    ) {
        // derive a sensible label from the name when one isn't given
        $this->label ??= Str::title(str_replace(['_', '-'], ' ', $name));
    }

    public static function make(string $name, ?string $label = null): static
    {
        return new static($name, $label);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function hideLabel(bool $hide = true): static
    {
        $this->hideLabel = $hide;

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function note(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function preNote(string $note): static
    {
        $this->preNote = $note;

        return $this;
    }

    /**
     * Set an arbitrary field attribute that has no dedicated method, for
     * forward compatibility with renderer options.
     */
    public function extra(string $key, mixed $value): static
    {
        $this->extra[$key] = $value;

        return $this;
    }

    /**
     * HTML/Vue attributes rendered onto the input (e.g. v-model, @keyup).
     */
    public function attrs(array $attrs): static
    {
        $this->extra['attrs'] = $attrs;

        return $this;
    }

    public function toArray(): array
    {
        $field = array_merge([
            'label' => $this->label,
            'name'  => $this->name,
        ], $this->extra);

        if ($this->type !== null) {
            $field['type'] = $this->type;
        }
        if ($this->required) {
            $field['required'] = true;
        }
        if ($this->hideLabel) {
            $field['hideLabel'] = true;
        }
        if ($this->options !== null) {
            $field['options'] = $this->options;
        }
        if ($this->note !== null) {
            $field['note'] = $this->note;
        }
        if ($this->preNote !== null) {
            $field['pre_note'] = $this->preNote;
        }

        return $field;
    }
}
