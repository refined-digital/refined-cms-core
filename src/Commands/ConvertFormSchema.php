<?php

namespace RefinedDigital\CMS\Commands;

use Illuminate\Console\Command;
use ReflectionClass;

/**
 * Converts a model's legacy $formFields / formFields() array into the fluent
 * formSchema() builder syntax, printing the generated code (and optionally
 * writing it back into the model file).
 */
class ConvertFormSchema extends Command
{
    protected $signature = 'refinedCMS:convert-form-schema {model : Model FQCN, e.g. "App\\\\RefinedCMS\\\\Blog\\\\Models\\\\Post"}
        {--write : Write the generated formSchema() into the model file (replacing the legacy form fields)}';

    protected $description = 'Convert a model\'s legacy $formFields array into the fluent formSchema() builder';

    /** maps a legacy field "type" to a dedicated field class + builder calls */
    protected array $typeMap = [
        'select'   => ['class' => 'Select',     'method' => null],
        'richtext' => ['class' => 'RichEditor', 'method' => null],
        'image'    => ['class' => 'Image',      'method' => null],
        'file'     => ['class' => 'FileUpload', 'method' => null],
        'password' => ['class' => 'Password',   'method' => null],
        'textarea' => ['class' => 'Textarea',   'method' => null],
        'email'    => ['class' => 'TextInput',  'method' => 'email'],
        'url'      => ['class' => 'TextInput',  'method' => 'url'],
        'number'   => ['class' => 'TextInput',  'method' => 'number'],
    ];

    protected array $usedClasses = [];

    public function handle(): int
    {
        $model = $this->resolveModelClass($this->argument('model'));

        if ($model === null) {
            $this->error('Model class not found. Pass the fully-qualified class name, e.g.');
            $this->line('  php artisan refinedCMS:convert-form-schema "App\\\\RefinedCMS\\\\Projects\\\\Models\\\\Project"');
            $this->line('  (or use forward slashes: "App/RefinedCMS/Projects/Models/Project")');

            return self::FAILURE;
        }

        $fields = $this->resolveLegacyFields($model);
        if ($fields === null) {
            $this->error("{$model} has no legacy \$formFields / formFields() to convert (it may already use formSchema()).");

            return self::FAILURE;
        }

        $this->usedClasses = [];
        $body = $this->generateTabs($fields);
        $imports = $this->buildImports();

        $method = "    public function formSchema(): array\n    {\n        return [\n{$body}        ];\n    }";

        $this->line('');
        $this->info('Add these imports below your namespace:');
        $this->line('');
        $this->line($imports);
        $this->line('');
        $this->info('Replace the legacy form fields with:');
        $this->line('');
        $this->line($method);
        $this->line('');

        if ($this->option('write')) {
            $this->writeToModel($model, $imports, $method);
        } else {
            $this->comment('Re-run with --write to apply this to the model file automatically.');
        }

        return self::SUCCESS;
    }

    /**
     * Resolve the model class from user input, tolerating forward-slash
     * separators (App/Foo/Bar) and shells that strip backslashes entirely.
     */
    protected function resolveModelClass(string $input): ?string
    {
        // as typed, and with forward slashes normalised to namespace separators
        foreach ([$input, str_replace('/', '\\', $input)] as $candidate) {
            $candidate = ltrim($candidate, '\\');
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        // backslashes were eaten by the shell (App\Foo\Bar -> AppFooBar): match
        // by comparing the separator-stripped name against every model file the
        // app's composer autoloader knows about
        $needle = strtolower(str_replace(['\\', '/'], '', $input));
        foreach ($this->knownClassNames() as $class) {
            if (strtolower(str_replace('\\', '', $class)) === $needle) {
                return $class;
            }
        }

        return null;
    }

    /**
     * All classes the composer classmap can autoload — lets us recover a model
     * whose namespace separators were stripped by the shell.
     */
    protected function knownClassNames(): array
    {
        $classes = get_declared_classes();

        foreach (spl_autoload_functions() ?: [] as $loader) {
            // composer's ClassLoader exposes its classmap; merge in its keys
            $instance = is_array($loader) ? ($loader[0] ?? null) : null;
            if (is_object($instance) && method_exists($instance, 'getClassMap')) {
                $classes = array_merge($classes, array_keys($instance->getClassMap()));
            }
        }

        return array_unique($classes);
    }

    /** read the legacy field array off the model (property or method) */
    protected function resolveLegacyFields(string $model): ?array
    {
        $instance = (new ReflectionClass($model))->newInstanceWithoutConstructor();

        if (method_exists($instance, 'formSchema')) {
            // already converted
            return null;
        }

        if (method_exists($instance, 'formFields')) {
            return $instance->formFields();
        }

        $ref = new ReflectionClass($model);
        if ($ref->hasProperty('formFields')) {
            $prop = $ref->getProperty('formFields');
            $prop->setAccessible(true);

            return $prop->getValue($instance);
        }

        return null;
    }

    protected function generateTabs(array $tabs, int $indent = 3): string
    {
        $pad = str_repeat('    ', $indent);
        $out = '';

        foreach ($tabs as $tab) {
            $name = $this->str($tab['name'] ?? 'Content');
            $out .= "{$pad}Tab::make({$name})->schema([\n";

            if (isset($tab['sections'])) {
                foreach ($tab['sections'] as $position => $section) {
                    $out .= $this->generateSection($position, $section['blocks'] ?? [], $indent + 1);
                }
            } elseif (isset($tab['blocks'])) {
                foreach ($tab['blocks'] as $block) {
                    $out .= $this->generateBlock($block, $indent + 1);
                }
            } elseif (isset($tab['fields'])) {
                $out .= $this->generateRows($tab['fields'], $indent + 1);
            }

            $out .= "{$pad}]),\n";
        }

        return $out;
    }

    protected function generateSection(string $position, array $blocks, int $indent): string
    {
        $pad = str_repeat('    ', $indent);
        $factory = in_array($position, ['left', 'right', 'bottom'], true)
            ? "Section::{$position}()"
            : "Section::make({$this->str($position)})";

        $this->use('Section');
        $out = "{$pad}{$factory}->schema([\n";
        foreach ($blocks as $block) {
            $out .= $this->generateBlock($block, $indent + 1);
        }
        $out .= "{$pad}]),\n";

        return $out;
    }

    protected function generateBlock(array $block, int $indent): string
    {
        $pad = str_repeat('    ', $indent);
        $this->use('Block');
        $name = isset($block['name']) ? $this->str($block['name']) : '';
        $out = "{$pad}Block::make({$name})->schema([\n";
        $out .= $this->generateRows($block['fields'] ?? [], $indent + 1);
        $out .= "{$pad}]),\n";

        return $out;
    }

    protected function generateRows(array $rows, int $indent): string
    {
        $pad = str_repeat('    ', $indent);
        $out = '';

        foreach ($rows as $row) {
            // a row is an array of field definitions
            if (count($row) === 1) {
                // single field — no Row wrapper needed
                $out .= "{$pad}" . $this->generateField($row[0]) . ",\n";
            } else {
                $this->use('Row');
                $out .= "{$pad}Row::make([\n";
                foreach ($row as $field) {
                    $out .= str_repeat('    ', $indent + 1) . $this->generateField($field) . ",\n";
                }
                $out .= "{$pad}]),\n";
            }
        }

        return $out;
    }

    protected function generateField(array $field): string
    {
        $type = $field['type'] ?? null;
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? null;

        $map = $type !== null && isset($this->typeMap[$type]) ? $this->typeMap[$type] : null;
        $class = $map['class'] ?? ($type === null ? 'TextInput' : 'Field');
        $this->use($class);

        $args = $this->str($name);
        if ($label !== null) {
            $args .= ', ' . $this->str($label);
        }
        $code = "{$class}::make({$args})";

        // dedicated builder method for the type (email/url/number), or generic ->type()
        if ($map && $map['method']) {
            $code .= "->{$map['method']}()";
        } elseif ($type !== null && ! $map) {
            $code .= "->type({$this->str($type)})";
        }

        if (! empty($field['required'])) {
            $code .= '->required()';
        }
        if (! empty($field['hideLabel'])) {
            $code .= '->hideLabel()';
        }
        if (isset($field['options'])) {
            $code .= '->options(' . $this->arr($field['options']) . ')';
        }
        if (isset($field['note'])) {
            $code .= '->note(' . $this->str($field['note']) . ')';
        }
        if (isset($field['pre_note'])) {
            $code .= '->preNote(' . $this->str($field['pre_note']) . ')';
        }
        if (isset($field['attrs'])) {
            $code .= '->attrs(' . $this->arr($field['attrs']) . ')';
        }

        return $code;
    }

    protected function buildImports(): string
    {
        $lines = ['use RefinedDigital\\CMS\\Modules\\Core\\Forms\\Tab;'];
        foreach (['Section', 'Block', 'Row'] as $container) {
            if (in_array($container, $this->usedClasses, true)) {
                $lines[] = "use RefinedDigital\\CMS\\Modules\\Core\\Forms\\{$container};";
            }
        }
        foreach (['Field', 'TextInput', 'Select', 'RichEditor', 'Image', 'FileUpload', 'Password', 'Textarea'] as $fieldClass) {
            if (in_array($fieldClass, $this->usedClasses, true)) {
                $lines[] = "use RefinedDigital\\CMS\\Modules\\Core\\Forms\\Fields\\{$fieldClass};";
            }
        }

        return implode("\n", $lines);
    }

    protected function writeToModel(string $model, string $imports, string $method): void
    {
        $file = (new ReflectionClass($model))->getFileName();
        $source = file_get_contents($file);

        // insert imports after the namespace line if not already present
        foreach (explode("\n", $imports) as $importLine) {
            if (! str_contains($source, trim($importLine))) {
                $source = preg_replace('/(namespace [^;]+;\n)/', "$1{$importLine}\n", $source, 1);
            }
        }

        // replace a legacy formFields() method OR $formFields property with formSchema()
        $replaced = preg_replace(
            '/(public|protected|private)\s+function\s+formFields\s*\([^)]*\)\s*(:\s*\w+)?\s*\{.*?\n\s*\}/s',
            ltrim($method),
            $source,
            1,
            $count
        );

        if ($count === 0) {
            $replaced = preg_replace(
                '/(public|protected|private)\s+\$formFields\s*=\s*\[.*?\];/s',
                ltrim($method),
                $source,
                1,
                $count
            );
        }

        if ($count === 0) {
            $this->warn("Could not auto-locate the legacy form fields in {$file}; paste the method manually.");

            return;
        }

        file_put_contents($file, $replaced);
        $this->info("Updated {$file}");
    }

    protected function use(string $class): void
    {
        if (! in_array($class, $this->usedClasses, true)) {
            $this->usedClasses[] = $class;
        }
    }

    /** export a scalar as PHP source */
    protected function str(mixed $value): string
    {
        return var_export($value, true);
    }

    /** export an array as compact PHP source */
    protected function arr(array $value): string
    {
        $parts = [];
        foreach ($value as $k => $v) {
            $key = is_int($k) ? $k : var_export($k, true);
            $val = is_array($v) ? $this->arr($v) : var_export($v, true);
            $parts[] = "{$key} => {$val}";
        }

        return '[' . implode(', ', $parts) . ']';
    }
}
