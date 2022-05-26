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
use Str;

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
    protected $siteName = null;

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
        $this->updatePackageJson();
        $this->askCpanel();
        $this->publishConfigs();
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
        $this->siteName = $siteName;


        // ask for the url
        $question = new Question('Site Url? (http://127.0.0.1:8000): ', 'http://127.0.0.1:8000');
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
        $this->setupDbDetails(false);


        $question = new ConfirmationQuestion('Do you want to send emails?: ', false);
        $mail = $helper->ask($this->input, $this->output, $question);

        $search = [
            '(APP_NAME=(.*?)\n)',
            '(APP_URL=(.*?)\n)',
        ];
        $replace = [
            "APP_NAME=\"".$siteName."\"\n",
            "APP_URL=".$siteUrl."\n",
        ];

        $emailDomain = null;
        $emailSecret = null;

        // email questions
        if ($mail == '1') {
            $search[] = '(MAIL_MAILER=(.*?)\n)';
            $search[] = '(MAIL_HOST=(.*?)\n)';
            $search[] = '(MAIL_PORT=(.*?)\n)';
            $search[] = '(MAIL_USERNAME=(.*?)\n)';
            $search[] = '(MAIL_FROM_NAME=(.*?)\n)';
            $search[] = '(MAIL_FROM_ADDRESS=(.*?)\n)';

            $replace[] = "MAIL_MAILER=smtp\n";
            $replace[] = "MAIL_HOST=smtp.sendgrid.net\n";
            $replace[] = "MAIL_PORT=587\n";
            $replace[] = "MAIL_USERNAME=apikey\n";

            $question = new Question('Domain? (mail.refinedcms.com): ', 'mail.refinedcms.com');
            $question->setValidator(function ($answer) {
                if(strlen($answer) < 1) {
                    throw new RuntimeException('SendGrid Domain is required');
                }
                return $answer;
            });
            $question->setMaxAttempts(3);
            $domain = $helper->ask($this->input, $this->output, $question);

            $replace[] = 'MAIL_FROM_NAME="'.$this->siteName."\"\n";
            $replace[] = 'MAIL_FROM_ADDRESS=no-reply@'.$domain."\n";


            $question = new Question('Mail Secret?: ', false);
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $question->setValidator(function ($answer) {
                if(strlen($answer) < 1) {
                    throw new RuntimeException('Mail Secret is required');
                }
                return $answer;
            });
            $question->setMaxAttempts(3);
            $secret = $helper->ask($this->input, $this->output, $question);

            $search[] = '(MAIL_PASSWORD=(.*?)\n)';
            $replace[] = "MAIL_PASSWORD=".$secret."\n";
        }


        $this->output->writeln('<info>Writing config</info>');
        // now do the search and replace on file strings
        $file = file_get_contents(app()->environmentFilePath());
        $file = preg_replace($search, $replace, $file);


        // add in the cache settings
        $file .= "
RESPONSE_CACHE_ENABLED=false
RESPONSE_CACHE_HEADER_NAME=\"".Str::slug($siteName)."\"
RESPONSE_CACHE_DRIVER=file
RESPONSE_CACHE_LIFETIME=".(60 * 60 * 24 * 7);
        file_put_contents(app()->environmentFilePath(), $file);

        // add in the scout config

        $file .= "
SCOUT_DRIVER=database
        ";

        $this->output->writeln('<info>Finished writing config</info>');
    }

    protected function setupDbDetails($output = true)
    {
        $helper = $this->getHelper('question');

        $databaseName = Str::slug('refined '.$this->siteName, '_');

        // database
        $question = new Question('Database? ('.$databaseName.'): ', $databaseName);
        $question->setValidator(function ($answer) {
            if(strlen($answer) < 1) {
                throw new RuntimeException('Database is required');
            }
            return $answer;
        });
        $dbName = $helper->ask($this->input, $this->output, $question);
        $dbName = Str::slug($dbName, '_');

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

        if ($output) {
            $this->output->writeln('<info>Writing db config</info>');
        }

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
        if ($output) {
            $this->output->writeln('<info>Finished writing db config</info>');
        }
    }


    protected function setupDb()
    {
        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion('Does the database already exist? ', false);

        if($helper->ask($this->input, $this->output, $question)) {
            return;
        }

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

        $db = new PDO('mysql:host='.env('DB_HOST').';', $this->dbUser, $this->dbPass);
        $db->exec('USE '.$this->dbName);

        // run migrations
        $newConfig = config()->get('database.connections.mysql');
        $newConfig['username'] = $this->dbUser;
        $newConfig['password'] = $this->dbPass;
        $newConfig['database'] = $this->dbName;
        config()->set('database.connections.temp', $newConfig);

        // create the user
        $data = [
            'created_at'    => "'".Carbon::now()."'",
            'updated_at'    => "'".Carbon::now()."'",
            'active'        => 1,
            'position'      => 0,
            'first_name'    => "'".$first."'",
            'last_name'     => "'".$last."'",
            'email'         => "'".$email."'",
            'password'      => "'".bcrypt($password)."'",
            'user_level_id' => $userLevelId
        ];

        $fields = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }
        $sql = 'INSERT INTO users ('.implode(', ', $fields).') VALUES ('.implode(', ', $values).')';
        $db->exec($sql);

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

        if (is_link($link)) {
            return;
        }

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

        if (is_link($link)) {
            return;
        }

        if (! windows_os()) {
            return symlink($target, $link);
        }

        $mode = is_dir($target) ? 'J' : 'H';

        exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
    }

    protected function regenerateKey()
    {
        $process = new Process(['php artisan key:generate']);
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

        $directories = [
            'views',
            'sass',
            'js'
        ];

        foreach ($directories as $directory) {
            $dir = $base.'/'.$directory;
            if (is_dir(resource_path($directory))) {
                exec('rm -R '.resource_path($directory));
            }
            exec('cp -R '.$dir.' '.resource_path($directory));
        }

        if (is_dir(resource_path('css'))) {
            exec('rm -R '.resource_path('css'));
        }

        unlink(base_path('webpack.mix.js'));
        file_put_contents(base_path('webpack.mix.js'), file_get_contents($base.'/webpack.mix.js'));

        if (file_exists(base_path('.prettierrc'))) {
            unlink(base_path('.prettierrc'));
        }
        file_put_contents(base_path('.prettierrc'), file_get_contents($base.'/.prettierrc'));
    }

    protected function enableCacheResponseMiddleware()
    {
        $dir = app_path('Http/Kernel.php');
        $file = file_get_contents($dir);
        $search = ['protected $routeMiddleware = ['];

        $options = [
            '\'doNotCacheResponse\' => \Spatie\ResponseCache\Middlewares\DoNotCacheResponse::class,',
            '\'cacheResponse\' => \Spatie\ResponseCache\Middlewares\CacheResponse::class,'
        ];

        $replaceString = 'protected $routeMiddleware = [';
        foreach ($options as $option) {
            $replaceString.= "\n\t\t". $option;
        }
        $replaceString .= '';

        $replace = [$replaceString];

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

    public function updateGitIgnore()
    {
        $content = '
.idea
.DS_Store
        ';
        $ignore = file_get_contents(base_path('.gitignore'));
        file_put_contents(base_path('.gitignore'), $ignore . $content);
    }

    public function askCpanel()
    {
        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion('Do you want to convert for cPanel?: ', false);
        $answer = $helper->ask($this->input, $this->output, $question);

        if ($answer) {
            $dir = public_path();
            $dirs = explode('/', $dir);
            if ($dirs[sizeof($dirs)-1] !== 'public_html') {
                Artisan::call('refinedCMS:convert-cpanel');
            }
        }
    }

    public function publishConfigs()
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'pages'
        ]);
        Artisan::call('vendor:publish', [
            '--provider' => 'Laravel\Scout\ScoutServiceProvider'
        ]);
    }

    public function updatePackageJson()
    {

        $contents = json_decode(file_get_contents(base_path('package.json'), true));

        $toDelete = [
            'axios',
            'postcss',
            'lodash'
        ];

        $toAdd = [
            'prettier' => '^2.5.0',
            'sass' => '^1.43.5',
            'sass-loader' => '^12.3.0'
        ];

        foreach ($toDelete as $package) {
            if (isset($contents->devDependencies->{$package})) {
                unset($contents->devDependencies->{$package});
            }
        }

        foreach ($toAdd as $package => $version) {
            if (!isset($contents->devDependencies->{$package})) {
                $contents->devDependencies->{$package} = $version;
            }
        }

        $toAdd = [
            '@fancyapps/ui' => '^4.0.9',
            '@splidejs/splide' =>  '^3.6.4'
        ];

        if (!isset($contents->dependencies)) {
            $contents->dependencies = new \stdClass();
        }

        foreach ($toAdd as $package => $version) {
            if (!isset($contents->dependencies->{$package})) {
                $contents->dependencies->{$package} = $version;
            }
        }

        $search = [
            '\/'
        ];
        $replace = [
            '/'
        ];

        $jsonContent = json_encode($contents, JSON_PRETTY_PRINT);
        $jsonContent = str_replace($search, $replace, $jsonContent);

        file_put_contents(base_path('package.json'), $jsonContent);
    }

    protected function cleanUpMigrations()
    {
        $filesToDelete = [
            '2014_10_12_000000_create_users_table',
            '2014_10_12_100000_create_password_resets_table',
            '2019_08_19_000000_create_failed_jobs_table',
            '2019_12_14_000001_create_personal_access_tokens_table'
        ];

        $path = database_path('migrations');
        foreach ($filesToDelete as $file) {
            $filePath = $path.'/'.$file.'.php';
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
