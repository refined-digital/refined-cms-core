<?php

namespace RefinedDigital\CMS\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use Validator;
use RuntimeException;
use PDO;
use Artisan;
use DB;

class InstallDatabase extends Install
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refinedCMS:install-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the Database';

     /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion('Do you need to set the Database details? ', false);

        if ($helper->ask($this->input, $this->output, $question)) {
            $this->setupDbDetails();
        } else {
            // set the database details
            $this->dbName = env('DB_DATABASE');
            $this->dbUser = env('DB_USERNAME');
            $this->dbPass = env('DB_PASSWORD');
        }

        $this->setupDb();
        $this->addUser();
    }
}
