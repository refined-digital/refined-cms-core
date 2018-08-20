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

class InstallSymLink extends Install
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refinedCMS:symlink';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the Admin Symlink';

     /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createSymLink();
    }
}
