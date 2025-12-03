<?php

namespace RefinedDigital\CMS\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use File;

class CreateContentBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:content-block {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Content Block';

    protected string $appPath = '';
    protected string $names = '';
    protected string $nameWords = '';
    protected string $readableName = '';
    protected string $nameKebab = '';
    protected array $search = [];
    protected array $replace = [];


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();

        // check if the direction already exists
        if (is_dir($this->appPath)) {
            $this->error($this->names.' content block already exists');

            return 0;
        }

        $this->copyDirectory();

        $this->setStubs();

        $this->writeToApp();
    }

    private function init()
    {
        $this->appPath = app_path('RefinedCMS/Content');
        $this->readableName = $this->argument('name');

        $this->names = \Str::studly($this->readableName);
        $this->nameWords = \Str::headline($this->readableName);
        $this->nameKebab = \Str::kebab($this->readableName);
        $this->nameCamel = \Str::camel($this->readableName);

        // check if there is the refined directory
        if (!is_dir($this->appPath)) {
            mkdir($this->appPath);
        }

        // add the name to the app path
        $this->appPath .= '/Blocks/'.$this->names;


        $this->search = [
            '{{Name}}',
            '{{NameWords}}',
            '{{nameKebab}}',
            '{{nameCamel}}',
        ];

        $this->replace = [
            $this->names,
            $this->nameWords,
            $this->nameKebab,
            $this->nameCamel,
        ];

    }

    private function copyDirectory()
    {
        $this->info('Copying Module Files');
        exec('cp -r '.base_path('vendor/refineddigital/cms/src/Modules/Content/Stubs/Content').' '.$this->appPath);
    }

    private function setStubs()
    {
        $this->info('Updating Files');

        // format the files
        $folders = scandir($this->appPath);
        if (sizeof($folders)) {
            array_shift($folders);array_shift($folders);
            if (sizeof($folders)) {
                foreach ($folders as $folder) {
                    $this->formatFiles($this->appPath.'/'.$folder);
                }
            }
        }
    }

    private function formatFiles($path)
    {
        if (is_dir($path)) {
            $files = scandir($path);
            if (sizeof($files)) {
                array_shift($files);array_shift($files);
                if (sizeof($files)) {
                    foreach ($files as $file) {
                        $this->formatFiles($path.'/'.$file);
                    }
                }
            }
        } else {
            $this->updateFile($path);

            // rename the file
            rename($path, str_replace($this->search, $this->replace, $path));
        }
    }

    private function updateFile($path)
    {
        // replace keywords
        $fileContents = file_get_contents($path);
        $fileContents = str_replace($this->search,$this->replace,$fileContents);

        // save the file
        file_put_contents($path,$fileContents);
    }

    private function writeToApp()
    {
        $this->info('Installing Module');

        $appFile = app_path('RefinedCMS/Content/Providers/ContentServiceProvider.php');

        // get the contents of the file
        $appData = file_get_contents($appFile);

        $search = [
            "// register the content fields",
            "use Illuminate\Support\ServiceProvider;"
        ];
        $replace = [
            '// register the content fields'."\n\t\t".'$agg->register('.$this->names.'::class);',
            'use App\\RefinedCMS\\Content\\Blocks\\'.$this->names.'\\'.$this->names.";\nuse Illuminate\Support\ServiceProvider;"
        ];

        $appData = str_replace($search, $replace, $appData);

        file_put_contents($appFile, $appData);
    }
}
