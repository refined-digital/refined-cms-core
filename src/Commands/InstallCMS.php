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

class InstallCMS extends Install
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refinedCMS:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the CMS';

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
        $this->linkStorage();
        $this->setupDb();
        $this->addSuperUser();
    }
}
