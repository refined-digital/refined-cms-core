<?php

namespace RefinedDigital\CMS\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use Validator;
use RuntimeException;
use PDO;
use Artisan;
use DB;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refinedCMS:install-full';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the CMS';

    protected $dbName = null;
    protected $dbUser = null;
    protected $dbPass = null;
    protected $userAdded = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->askQuestions();
        $this->regenerateKey();
        $this->createSymLink();
        $this->setupDb();
        $this->copyTemplates();
        $this->addUser();
    }

    protected function askQuestions()
    {
        $helper = $this->getHelper('question');

        // site name
        $question = new Question('Site Name?: ', false);
        $question->setValidator(function ($answer) {
            if(strlen($answer) < 1) {
                throw new RuntimeException('Site Name is required');
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $siteName = $helper->ask($this->input, $this->output, $question);

        // ask for the url
        $question = new Question('Site Url?: ', false);
        $question->setValidator(function($answer) {
            if (strlen($answer) < 1) {
                throw new RuntimeException('Site URL is required');
            }

            $siteBits = explode('.', $answer);
            if(count($siteBits) < 3) {
                throw new RuntimeException("Site url must contain a sub domain, domain and tdl, ie: www.domain.com\nYou supplied: ".$answer);
            }

            return $answer;
        });
        $question->setMaxAttempts(3);
        $siteUrl = $helper->ask($this->input, $this->output, $question);

        // if there is no http, then add the site url
        if(!is_numeric(strpos($siteUrl, 'http://')) && !is_numeric(strpos($siteUrl, 'https://'))) {
            $siteUrl = 'http://'.$siteUrl;
        }


        // database
        $this->setupDbDetails();

        $this->output->writeln('<info>Writing config</info>');
        // now do the search and replace on file strings
        $file = file_get_contents(app()->environmentFilePath());
        $search = [
            '(APP_NAME=(.*?)\n)',
            '(APP_URL=(.*?)\n)',
            '(MAIL_NAME=(.*?)\n)',
            '(MAIL_DRIVER=(.*?)\n)',
            '(MAIL_ENCRYPTION=(.*?)\n)',
        ];
        $replace = [
            "APP_NAME=\"".$siteName."\"\n",
            "APP_URL=".$siteUrl."\n",
            "MAIL_NAME='".$siteName."'\n",
            "MAIL_DRIVER=mailgun\n",
            "MAIL_ENCRYPTION=null
MAIL_FROM_NAME='RefinedCMS'
MAIL_FROM_ADDRESS=no-reply@mg.refineddigital.co.nz
MAILGUN_DOMAIN=mg.refineddigital.co.nz
MAILGUN_SECRET=key-d72898ceed103fd84f6f3f9774c2b018\n",
        ];
        $file = preg_replace($search, $replace, $file);

        // add in the cache settings
        $file .= "
RESPONSE_CACHE_ENABLED=true
RESPONSE_CACHE_HEADER_NAME=\"".str_slug($siteName)."\"
RESPONSE_CACHE_DRIVER=file
RESPONSE_CACHE_LIFETIME=".(60 * 60 * 24 * 7);
        file_put_contents(app()->environmentFilePath(), $file);

        $this->output->writeln('<info>Finished writing config</info>');
    }

    protected function setupDbDetails()
    {
        $helper = $this->getHelper('question');

        // database
        $question = new Question('Database?: ', false);
        $question->setValidator(function ($answer) {
            if(strlen($answer) < 1) {
                throw new RuntimeException('Database is required');
            }
            return $answer;
        });
        $dbName = $helper->ask($this->input, $this->output, $question);
        $dbName = str_slug($dbName, '_');

        // database user
        $question = new Question('Database User?: ', false);
        $question->setValidator(function ($answer) {
            if(strlen($answer) < 1) {
                throw new RuntimeException('Database user is required');
            }
            return $answer;
        });
        $dbUser = $helper->ask($this->input, $this->output, $question);

        // database password
        $question = new Question('Database Password?: ', false);
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(function ($answer) {
            if(strlen($answer) < 1) {
                throw new RuntimeException('Database password is required');
            }
            return $answer;
        });
        $dbPassword = $helper->ask($this->input, $this->output, $question);

        $this->output->writeln('<info>Writing db config</info>');
        // now do the search and replace on file strings
        $file = file_get_contents(app()->environmentFilePath());
        $search = [
            '(DB_DATABASE=(.*?)\n)',
            '(DB_USERNAME=(.*?)\n)',
            '(DB_PASSWORD=(.*?)\n)',
        ];
        $replace = [
            "DB_DATABASE=".$dbName."\n",
            "DB_USERNAME=".$dbUser."\n",
            "DB_PASSWORD=\"".$dbPassword."\"\n",
        ];
        file_put_contents(app()->environmentFilePath(), preg_replace($search, $replace, $file));

        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPassword;
        $this->output->writeln('<info>Finished writing db config</info>');
    }


    protected function setupDb()
    {
        $this->output->writeln('<info>Installing the Database</info>');
        $db = new PDO('mysql:host='.env('DB_HOST').';', $this->dbUser, $this->dbPass);
        $db->exec('CREATE DATABASE `'.$this->dbName.'`;');

        // run migrations
        $this->output->writeln('<info>Run the migrations</info>');
        $newConfig = config()->get('database.connections.mysql');
        $newConfig['username'] = $this->dbUser;
        $newConfig['password'] = $this->dbPass;
        $newConfig['database'] = $this->dbName;
        config()->set('database.connections.temp', $newConfig);

        Artisan::call('migrate:install', [
            '--database' => 'temp'
        ]);

        $this->output->writeln('<info>Migrating the database</info>');
        Artisan::call('migrate', [
            '--path' => 'vendor/refineddigital/cms/src/Database/Migrations',
            '--database' => 'temp',
            '--force' => 1,
        ]);

        $this->output->writeln('<info>Seeding the database</info>');
        Artisan::call('db:seed', [
            '--class' => '\\RefinedDigital\\CMS\\Database\\Seeds\\RefinedDatabaseSeeder',
            '--database' => 'temp',
            '--force' => 1
        ]);
    }

    protected function addUser()
    {
        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion('Do you want to add '.($this->userAdded ? 'another' : 'a').' User? ', false);

        if(!$helper->ask($this->input, $this->output, $question)) {
            return;
        }

        // first name
        $question = new Question('First Name: ', false);
        $question->setValidator(function ($answer) {
            if(strlen($answer) < 1) {
                throw new RuntimeException('First Name is required');
            }
            return $answer;
        });
        $first = $helper->ask($this->input, $this->output, $question);

        // last name
        $question = new Question('Last Name: ', false);
        $question->setValidator(function ($answer) {
            if(strlen($answer) < 1) {
                throw new RuntimeException('Last Name is required');
            }
            return $answer;
        });
        $last = $helper->ask($this->input, $this->output, $question);

        // email
        $question = new Question('Email: ', false);
        $question->setValidator(function ($answer) {
            $validator = Validator::make(['email' => $answer], [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                throw new RuntimeException('Email is required, and must be a valid email');
            }

            return $answer;
        });
        $email = $helper->ask($this->input, $this->output, $question);

        // password
        $question = new Question('Password: ', false);
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(function ($answer) {
            $validator = Validator::make(['password' => $answer], [
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                throw new RuntimeException('Password must be at least 6 characters');
            }
            return $answer;
        });
        $password = $helper->ask($this->input, $this->output, $question);

        // user level
        $question = new ChoiceQuestion('User Level: ', [1 => 'Super Admin', 2 => 'Admin', 3 => 'Member'], null);
        $question->setErrorMessage('User Level %s is invalid.');
        $userLevel = $helper->ask($this->input, $this->output, $question);
        $userLevelId = 3;
        switch($userLevel) {
            case 'Super Admin': $userLevelId = 1; break;
            case 'Admin': $userLevelId = 2; break;
        }


        // create the user
        DB::table('users')->insert([
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
            'active'        => 1,
            'position'      => 0,
            'first_name'    => $first,
            'last_name'     => $last,
            'email'         => $email,
            'password'      => bcrypt($password),
            'user_level_id' => $userLevelId
        ]);

        $this->userAdded = true;
        $this->addUser();
    }

    protected function createSymLink()
    {
        $link = public_path('vendor/');
        $target = '../../../vendor/refineddigital/cms/assets/';

        // create the directories
        if (!is_dir($link)) {
            mkdir($link);
        }
        $link .= 'refined/';
        if (!is_dir($link)) {
            mkdir($link);
        }
        $link .= 'core';

        if (! windows_os()) {
            return symlink($target, $link);
        }

        $mode = is_dir($target) ? 'J' : 'H';

        exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
    }

    protected function linkStorage()
    {
        $link = public_path('storage');
        $target = '../storage/app/public/';

        if (! windows_os()) {
            return symlink($target, $link);
        }

        $mode = is_dir($target) ? 'J' : 'H';

        exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
    }

    protected function regenerateKey()
    {
        $process = new Process('php artisan key:generate');
        $process->run();
    }

    protected function setupComplete()
    {
        $this->output->writeln('<comment>Setup Complete!</comment>');
    }

    protected function copyTemplates()
    {
        $this->output->writeln('<info>Copying Templates</info>');
        $base = base_path('vendor/refineddigital/cms/src/Commands/defaults');

        $dir = $base.'/views';
        exec('rm -R '.resource_path('views'));
        exec('cp -R '.$dir.' '.resource_path('views'));

        $dir = $base.'/sass';
        exec('rm -R '.resource_path('sass'));
        exec('cp -R '.$dir.' '.resource_path('sass'));

        $dir = $base.'/js';
        exec('rm -R '.resource_path('js'));
        exec('cp -R '.$dir.' '.resource_path('js'));

        unlink(base_path('webpack.mix.js'));
        file_put_contents(base_path('webpack.mix.js'), file_get_contents($base.'/webpack.mix.js'));

        /*
        $files = scandir($dir);
        array_shift($files);array_shift($files);
        $this->copy($files, $dir, 'views/layouts');

        $dir = __DIR__.'/defaults/views/templates';
        $files = scandir($dir);
        array_shift($files);array_shift($files);
        $this->copy($files, $dir, 'views/templates');
*/
    }

    protected function enableCacheResponseMiddleware()
    {
        $dir = app_path('Http/Kernel.php');
        $file = file_get_contents($dir);
        $search = ['protected $routeMiddleware = ['];
        $replace = ['protected $routeMiddleware = [' . "\n\t\t". '\'cacheResponse\' => \Spatie\ResponseCache\Middlewares\CacheResponse::class,'];

        $file = str_replace($search, $replace, $file);

        file_put_contents($dir, $file);
    }

    private function copy($files, $dir, $public)
    {
        if (!is_dir(resource_path($public))) {
            mkdir(resource_path($public));
        }

        if (sizeof($files)) {
            try {
                foreach ($files as $file) {
                    $contents = file_get_contents($dir.$file);
                    file_put_contents(resource_path($public.'/'.$file), $contents);
                }
            } catch(\Exception $e) {
                $this->output->writeln('<error>Failed to copy all templates</error>');
            }
        }

    }
}
