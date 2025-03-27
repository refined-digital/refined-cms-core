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
        $this->setCache();
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
            '(LOG_CHANNEL=(.*?)\n)',
            '(SESSION_DRIVER=(.*?)\n)',
            '(QUEUE_CONNECTION=(.*?)\n)',
        ];
        $replace = [
            "APP_NAME=\"".$siteName."\"\n",
            "APP_URL=".$siteUrl."\n",
            "LOG_CHANNEL=daily\n",
            "SESSION_DRIVER=file\n",
            "QUEUE_CONNECTION=sync\n",
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

        file_put_contents(app()->environmentFilePath(), $file);


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

        $envPath = app()->environmentFilePath();

        // now do the search and replace on file strings
        $file = file_get_contents($envPath);
        $search = [
            '(DB_DATABASE=(.*?)\n)',
            '(DB_USERNAME=(.*?)\n)',
            '(DB_PASSWORD=(.*?)\n)',
            '(DB_CONNECTION=(.*?)\n)',
        ];
        $replace = [
            "DB_DATABASE=".$dbName."\n",
            "DB_USERNAME=".$dbUser."\n",
            "DB_PASSWORD=\"".$dbPassword."\"\n",
            "DB_CONNECTION=mysql\n",
        ];

        $file = preg_replace($search, $replace, $file);

        // remove the commented out db files
        $search = [
            '# DB_HOST',
            '# DB_PORT',
            '# DB_DATABASE',
            '# DB_USERNAME',
            '# DB_PASSWORD',
            '#DB_HOST',
            '#DB_PORT',
            '#DB_DATABASE',
            '#DB_USERNAME',
            '#DB_PASSWORD',
        ];

        $replace = [
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ];

        $file = str_replace($search, $replace, $file);

        file_put_contents($envPath, $file);

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
            'css',
            'js',
            'svg'
        ];

        foreach ($directories as $directory) {
            $dir = $base.'/'.$directory;
            if (is_dir(resource_path($directory))) {
                exec('rm -R '.resource_path($directory));
            }
            exec('cp -R '.$dir.' '.resource_path($directory));
        }

        if (file_exists(base_path('vite.config.js'))) {
            unlink(base_path('vite.config.js'));
        }
        file_put_contents(base_path('vite.config.js'), file_get_contents($base.'/vite.config.js'));

        if (file_exists(base_path('postcss.config.js'))) {
            unlink(base_path('postcss.config.js'));
        }
        file_put_contents(base_path('postcss.config.js'), file_get_contents($base.'/postcss.config.js'));

        if (file_exists(base_path('.prettierrc'))) {
            unlink(base_path('.prettierrc'));
        }
        file_put_contents(base_path('.prettierrc'), file_get_contents($base.'/.prettierrc'));

        if (file_exists(base_path('.php-cs-fixer.php'))) {
            unlink(base_path('.php-cs-fixer.php'));
        }
        file_put_contents(base_path('.php-cs-fixer.php'), file_get_contents($base.'/.php-cs-fixer.php'));

        // copy husky
        if (is_dir(base_path('.husky'))) {
            exec('rm -R '.base_path('.husky'));
        }
        exec('cp -R '.$base.'/.husky '.base_path('.husky'));
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

    public function copyContentBlocks()
    {
        $this->output->writeln('<info>Copying Content Blocks</info>');

        $from = base_path('vendor/refineddigital/cms/src/Commands/defaults/app/RefinedCMS/Content');
        $to = app_path('RefinedCMS');

        if (!is_dir($to)) {
            mkdir($to);
        }


        exec('cp -R '.$from.' '.$to);
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

    public function askNpm()
    {
        $this->runCommands(['node -v > .nvmrc']);

        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion('Do you want to install npm?: ', false);
        $answer = $helper->ask($this->input, $this->output, $question);

        if ($answer) {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    public function publishConfigs()
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'pages'
        ]);
    }

    public function updatePackageJson()
    {

        $contents = json_decode(file_get_contents(base_path('package.json'), true));

        $toDelete = [
            'axios',
            'vite',
            'laravel-vite-plugin'
        ];

        $toAdd = [
            'glob' => '^11.0.0',
            'laravel-vite-plugin' => '1.0.2',
            'postcss-discard-comments' => '^7.0.0',
            'postcss-nested' => '^6.0.1',
            'postcss-preset-env' => '^9.5.9',
            'prettier' => '^3.2.5',
            'vite' => '^5.2.1',
            'husky' => '^9.0.11',
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
            '@fancyapps/ui' => '^5.0.36',
            'swiper' => '^11.1.1'
        ];

        if (!isset($contents->dependencies)) {
            $contents->dependencies = new \stdClass();
        }

        foreach ($toAdd as $package => $version) {
            if (!isset($contents->dependencies->{$package})) {
                $contents->dependencies->{$package} = $version;
            }
        }

        // add husky related commands
        $contents->scripts->prepare = 'husky';

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
            '0001_01_01_000000_create_users_table',
            '0001_01_01_000001_create_cache_table',
            '0001_01_01_000002_create_jobs_table',0
        ];

        $path = database_path('migrations');
        foreach ($filesToDelete as $file) {
            $filePath = $path.'/'.$file.'.php';
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    protected function removeWelcomeRoute()
    {
        $file = base_path('routes/web.php');
        $contents = file_get_contents($file);
        $contents = str_replace("Route::get('/', function () {\n    return view('welcome');\n});", '', $contents);
        file_put_contents($file, $contents);
    }

    protected function addErrorHandler()
    {
        $appFile = base_path('bootstrap/app.php');

        // get the contents of the file
        $appData = file_get_contents($appFile);

        $search = '->withExceptions(function (Exceptions $exceptions) {';
        $replace = '->withExceptions(function (Exceptions $exceptions) {'."\n\t".'refinedErrorHandler()->register($exceptions);';

        $appData = str_replace($search, $replace, $appData);

        file_put_contents($appFile, $appData);
    }

    protected function setCache()
    {
        $this->output->writeln('<info>Updating gitignore to ignore cache</info>');

        $ignore = '
public/page-cache
    ';

        $ignore = file_get_contents(base_path('.gitignore'));
        file_put_contents(base_path('.gitignore'), $ignore . $ignore);

        $this->output->writeln('<info>Writing to .htaccess</info>');

        $contents = file_get_contents(public_path('.htaccess'));
        $search = [
            'RewriteEngine On',
            '# Send Requests To Front Controller...',
        ];

        $replace = [
            "RewriteEngine On

RedirectMatch 404 ^/page-cache",
            "RewriteCond %{REQUEST_URI} ^/?$
    RewriteCond %{DOCUMENT_ROOT}/page-cache/pc__index__pc.html -f
    RewriteRule .? page-cache/pc__index__pc.html [L]
    RewriteCond %{DOCUMENT_ROOT}/page-cache%{REQUEST_URI}.html -f
    RewriteRule . page-cache%{REQUEST_URI}.html [L]

    # Send Requests To Front Controller...",
        ];


        file_put_contents(public_path('.htaccess'), str_replace($search, $replace, $contents));

    }

    /**
     * Run the given commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function runCommands($commands)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }

}
