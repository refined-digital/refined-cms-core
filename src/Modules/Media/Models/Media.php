<?php

namespace RefinedDigital\CMS\Modules\Media\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RefinedDigital\CMS\Modules\Core\Models\CoreModel;
use RefinedDigital\CMS\Modules\Media\Traits\SortableMediaTrait;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;
use File;

class Media extends CoreModel implements Sortable {
    use SortableMediaTrait, SoftDeletes;

    protected $cacheSecondsHigh = 60 * 24 * 7;
    protected $cacheSecondsLow = 60 * 24;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'active',
        'position',
        'media_category_id',
        'name',
        'file',
        'alt',
        'description',
        'mime',
        'external_id',
        'external_url'
    ];

    protected $appends = [
        'link',
        'extension',
        'type',
        'size',
    ];

    protected $casts = [
        'id' => 'integer',
        'active' => 'integer',
        'position' => 'integer',
        'media_category_id' => 'integer',
    ];

    protected $videoTypes = [
        'mp4'
    ];

    /** [from, to] of every file the current rename has moved, for undoing it */
    protected array $movedFiles = [];

    /**
     * Tables the url rewrite never touches: php-serialised payloads, where a
     * replacement of a different length corrupts the string, and the audit log,
     * which records what was written at the time and must stay that way.
     */
    protected $urlRewriteSkipTables = [
        'activity_log',
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
    ];

    protected $table = 'media';

    protected static function booted()
    {
        static::updating(function (self $media) {
            $original = $media->getOriginal('file');

            if ($original && $media->isDirty('file')) {
                $media->renameStoredFiles($original, $media->file);
            }
        });
    }

    /**
     * A rename touches the file, its derivatives, every url saved in content and
     * this row, so it is wrapped in a transaction - the updating hook does its
     * work before the row itself is written, and a failure part way through must
     * not leave content pointing at a name the row does not have.
     *
     * The database rolls itself back, the disk it knows nothing about, so the
     * moves are undone here. This sits around the transaction rather than around
     * the moves so it also catches a failure writing the row itself, which
     * happens after the hook has already renamed everything.
     */
    public function save(array $options = [])
    {
        if (! $this->exists || ! $this->isDirty('file') || ! $this->getOriginal('file')) {
            return parent::save($options);
        }

        $this->movedFiles = [];

        try {
            return DB::transaction(fn () => parent::save($options));
        } catch (\Throwable $error) {
            $this->revertMovedFiles();

            throw $error;
        }
    }

    /**
     * Puts back every file the rename moved, newest move first.
     */
    private function revertMovedFiles(): void
    {
        $disk = Storage::disk($this->getDisk());

        foreach (array_reverse($this->movedFiles) as [$from, $to]) {
            if ($disk->exists($to) && ! $disk->exists($from)) {
                $disk->move($to, $from);
            }
        }

        $this->movedFiles = [];
        $this->forgetFileCache();
    }

    /**
     * Keeps the stored name a slug and the extension the file's own. Only the
     * name is the admin's to set - the derivatives are generated off it, and a
     * typed extension changing the file type is an accident waiting to happen,
     * so an existing file keeps the extension it was uploaded with.
     */
    public function setFileAttribute($value)
    {
        $value = (string) $value;
        $extension = strtolower(pathinfo((string) ($this->attributes['file'] ?? ''), PATHINFO_EXTENSION));
        $typed = strtolower(pathinfo($value, PATHINFO_EXTENSION));

        // anything but the file's own extension is part of the name, so a rename
        // to "report.pdf" on a jpg gives report-pdf.jpg rather than a jpg
        // pretending to be a pdf
        $name = ($extension && $typed !== $extension)
            ? Str::slug($value)
            : Str::slug(pathinfo($value, PATHINFO_FILENAME));

        if (! $extension) {
            $extension = $typed;
        }

        // an empty name would leave a bare extension, so the current name stands
        if (! $name) {
            return;
        }

        $this->attributes['file'] = $extension ? $name.'.'.$extension : $name;
    }

    /**
     * Renames the upload and every derivative beside it. All of them live in the
     * media id's directory and are named off the original's base - the resizes,
     * the webp/avif variants, the video poster and encode - so a prefix rename
     * catches the lot without knowing how each was generated.
     */
    private function renameStoredFiles(string $oldFile, string $newFile): void
    {
        $oldBase = pathinfo($oldFile, PATHINFO_FILENAME);
        $newBase = pathinfo($newFile, PATHINFO_FILENAME);

        if (! $oldBase || ! $newBase || $oldBase === $newBase) {
            return;
        }

        $disk = Storage::disk($this->getDisk());

        foreach ($disk->files((string) $this->id) as $path) {
            $name = basename($path);

            if (! str_starts_with($name, $oldBase)) {
                continue;
            }

            $target = $this->id.DIRECTORY_SEPARATOR.$newBase.substr($name, strlen($oldBase));

            if ($path !== $target && ! $disk->exists($target)) {
                $disk->move($path, $target);
                // recorded so save() can put them back if anything later fails
                $this->movedFiles[] = [$path, $target];
            }
        }

        $this->rewriteStoredUrls($oldBase, $newBase);
        $this->forgetFileCache();
    }

    /**
     * Repoints urls already written into content. Rich text and link fields
     * store the url rather than the media id, so those break on a rename unless
     * they are rewritten.
     *
     * Every text column in the database is swept rather than a list of known
     * ones, so content in custom modules is covered without registering it. The
     * search is the file's storage path up to its base name, which catches the
     * derivatives alongside the original, and both the absolute urls and the
     * root-relative ones the admin writes.
     */
    private function rewriteStoredUrls(string $oldBase, string $newBase): void
    {
        $disk = Storage::disk($this->getDisk());
        $old = parse_url($disk->url($this->id.'/'.$oldBase), PHP_URL_PATH);
        $new = parse_url($disk->url($this->id.'/'.$newBase), PHP_URL_PATH);

        if (! $old || ! $new || $old === $new) {
            return;
        }

        $skip = array_merge($this->urlRewriteSkipTables, [$this->getTable()]);
        $textTypes = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'json', 'jsonb'];

        // json encoded into a text column escapes its slashes, and the escaped
        // form is what sits in the column. real json columns are normalised by
        // the database and match the plain form, so both are swept
        $searches = [
            [$old, $new],
            [str_replace('/', '\\/', $old), str_replace('/', '\\/', $new)],
        ];

        foreach (Schema::getTables() as $table) {
            $tableName = $table['name'];

            if (in_array($tableName, $skip)) {
                continue;
            }

            foreach (Schema::getColumns($tableName) as $column) {
                if (! in_array(strtolower($column['type_name']), $textTypes)) {
                    continue;
                }

                $columnName = $this->quoteIdentifier($column['name']);

                foreach ($searches as [$search, $replace]) {
                    // an explicit escape character is needed for the escaped
                    // form: by default a backslash in a like pattern escapes
                    // the character after it, so the pattern would never match
                    // the very rows it is looking for
                    DB::statement(
                        'update '.$this->quoteIdentifier($tableName)
                        .' set '.$columnName.' = replace('.$columnName.', ?, ?)'
                        .' where '.$columnName." like ? escape '~'",
                        [$search, $replace, '%'.$search.'%']
                    );
                }
            }
        }
    }

    /**
     * Identifiers come from the schema, so this only has to survive reserved
     * words, not injection.
     */
    private function quoteIdentifier(string $name): string
    {
        return DB::connection()->getQueryGrammar()->wrap($name);
    }

    /**
     * Drops the per-file caches, which all key off the path and so go stale the
     * moment the file is renamed.
     */
    public function forgetFileCache(): void
    {
        foreach (['path', 'url', 'extension', 'file-size', 'exitst'] as $key) {
            Cache::forget('media-file-'.$this->id.'-'.$key);
        }
    }

    public function getLinkAttribute() {
        $link           = new \stdClass();
        $link->thumb    = $this->type === 'Image'
            ? image()->load($this->id)->width(500)->string()
            : null
        ;
        $link->original = $this->getFileUrl();
        $link->basePath = pages()->getBaseHref();

        return $link;
    }

    public function getTypeAttribute() {
        $extension = $this->extension;
        $type      = is_numeric(strpos($this->mime, 'image/')) ? 'Image' : 'File';
        if (in_array($extension, $this->videoTypes)) {
            $type = 'Video';
        }

        return $type;
    }

    public function getSizeAttribute() {
        if (!$this->exists()) {
            return null;
        }

        try {
            return Cache::flexible(
                'media-file-'.$this->id.'-file-size',
                [$this->cacheSecondsLow, $this->cacheSecondsHigh],
                fn () => help()->formatBytes(File::size($this->getFilePath())))
            ;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getExtensionAttribute() {
        return Cache::flexible(
            'media-file-'.$this->id.'-extension',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => pathinfo($this->getFilePath(), PATHINFO_EXTENSION))
        ;
    }

    public function getFileWithDirectory(string $name = '')
    {
        if (!$name) {
            $name = $this->file;
        }

        return $this->id . DIRECTORY_SEPARATOR . $name;
    }

    private function getFilePath()
    {
        return Cache::flexible(
            'media-file-'.$this->id.'-path',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => Storage::disk($this->getDisk())->path($this->getFileWithDirectory()))
        ;
    }

    private function getFileUrl()
    {
        return Cache::flexible(
            'media-file-'.$this->id.'-url',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => Storage::disk($this->getDisk())->url($this->getFileWithDirectory()))
        ;
    }

    private function getDisk()
    {
        return config('pages.image.disk');
    }

    private function exists()
    {
        return Cache::flexible(
            'media-file-'.$this->id.'-exitst',
            [$this->cacheSecondsLow, $this->cacheSecondsHigh],
            fn () => Storage::disk($this->getDisk())->exists($this->getFileWithDirectory()))
        ;
    }
}
