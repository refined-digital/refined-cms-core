<?php

namespace RefinedDigital\CMS\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
use File;

class CreateModule extends Install
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refinedCMS:create-module {name}
        {--isPage= : Does the module need to be page enabled?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Admin Module';

    protected $appPath = null;
    protected $readableName = null;
    protected $name = null;
    protected $names = null;
    protected $fullName = null;
    protected $snakeNames = null;
    protected $search = [];
    protected $replace = [];
    protected $isPage = false;
    protected $templateId = 0;


     /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();
        $this->copyDirectory();

        if ($this->isPage) {
            $this->createTemplates();
        }

        $this->setStubs();
        $this->createMigration();

        $this->writeToApp();

    }

    private function init()
    {
        $this->appPath = app_path('RefinedCMS');
        $this->isPage = $this->option('isPage');
        $this->readableName = $this->argument('name');

        $this->name = str_singular( str_slug($this->readableName, ' ') );
        $this->names = str_plural( str_slug($this->readableName, ' ') );

        $this->fullName = studly_case($this->readableName);

        $this->snakeNames = snake_case(str_plural($this->readableName));

        // check if there is the refind directory
        if (!is_dir($this->appPath)) {
            mkdir($this->appPath);
        }

        // add the name to the app path
        $this->appPath .= '/'.$this->fullName;


        $this->search = [
            '{{name}}',
            '{{names}}',
            '{{Name}}',
            '{{FullName}}',
            '{{ReadableName}}',
            '{{SnakeNames}}',
            '{{templateId}}',
        ];

        $this->replace = [
            $this->name,
            $this->names,
            studly_case($this->name),
            $this->fullName,
            $this->readableName,
            $this->snakeNames,
        ];

    }

    private function copyDirectory()
    {
        $this->info('Copying Module Files');
        exec('cp -r '.base_path('vendor/refineddigital/cms/src/Modules/Core/Resources/Stubs').' '.$this->appPath);
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

        // check if there is an {-page -} option
        preg_match_all('/(?:{-page((?:.*?\r?\n?)*)-})/', $fileContents, $out);
        if (sizeof($out)) {
            if ($this->isPage) {
                $fileContents = str_replace($out[0], $out[1], $fileContents);
            } else {
                $fileContents = str_replace($out[0], '', $fileContents);
            }
        }


        // save the file
        file_put_contents($path,$fileContents);
    }

    private function createMigration()
    {
        $this->info('Creating Migration File');
        $newFile = database_path('migrations/'.date('Y_m_d_His').'_create_'.str_slug($this->Name, '_').'_table.php');
        copy($this->appPath.'/Database/create_table.php', $newFile);
        $this->updateFile($newFile);

        // remove the folder
        exec('rm -R '.$this->appPath.'/Database');
    }

    private function createTemplates()
    {
        $this->info('Creating Template Files');

            // add the templates
            $position = DB::table('templates')->count();
            DB::table('templates')->insert(
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'name' => $this->readableName, 'source' => $this->name, 'position' => $position]
            );
            $this->templateId = DB::table('templates')->insertGetId(
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'name' => $this->readableName.' Details', 'source' => str_slug($this->name.' details'), 'position' => $position+1, 'active' => 0]
            );
            $this->replace[] = $this->templateId;
            // create the templates
            $html = "@extends('layouts.index')

@section('template')

    @include('templates.includes.content')
		
@stop";
            File::put(resource_path().'/views/templates/'.str_slug($this->name).'.blade.php', $html);
            File::put(resource_path().'/views/templates/'.str_slug($this->name.' details').'.blade.php', $html);
    }

    private function writeToApp()
    {
        $this->info('Installing Module');

        $appFile = config_path('app.php');

        // get the contents of the file
        $appData = File::get($appFile);

        // find the last occurrence of App\Amped
        preg_match_all("/\\/\*[\s\S]*?\*\//", $appData, $matches);
        if (sizeof($matches)) {
            $position = null;
            foreach ($matches[0] as $key => $value) {
                if (is_numeric(strpos($value, '* Package Service Providers...'))) {
                    $position = $key;
                }
            }

            if ($position) {
                $search = $matches[0][$position];
                $replace = $search.PHP_EOL."\t\t".'App\RefinedCMS\\'.$this->fullName.'\Providers\\'.studly_case($this->name).'ServiceProvider::class,';

                // add the service provider
                $appData = str_replace($search, $replace, $appData);
                File::put($appFile, $appData);
            }
        }

    }
}
