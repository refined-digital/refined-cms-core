<?php

namespace RefinedDigital\CMS\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
use File;

class ConvertCpanel extends Install
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'refinedCMS:convert-cpanel';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Converts structure for cpanel';


   /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
      $this->gitignore();
      $this->updateFiles();
      $this->renamePublic();
  }

  private function gitignore()
  {
    $this->output->writeln('<info>Updating gitignore</info>');

    $cpanel = '
.bash_logout
.bash_profile
.bashrc
.cagefs/
.cl.selector/
.contactemail
.cpanel/
.cphorde/
.gemrc
.lastlogin
.spamassassin/
.spamassassinenable
.ssh/
.viminfo
.cache/
.config/
.gitconfig
.local/
.bash_history
.gitold/
.softaculous/
.vim/
.trash/
access-logs
etc/
logs/
mail/
ssl/
tmp/
var/
www    
    ';

    $ignore = file_get_contents(base_path('.gitignore'));
    file_put_contents(base_path('.gitignore'), $ignore . $cpanel);
  }

  private function renamePublic()
  {
    $this->output->writeln('<info>Renaming Public Folder</info>');
    rename(public_path(), base_path('public_html'));
  }

  private function updateFiles()
  {
    $this->output->writeln('<info>Updating files</info>');

    // vite.config
    if (file_get_contents(base_path('vite.config.js'))) {
        $contents = file_get_contents(base_path('vite.config.js'));
        $search = ["'public'"];
        $replace = ["'public_html'"];
        $contents = str_replace($search, $replace, $contents);
        file_put_contents(base_path('vite.config.js'), $contents);
    }

    // bootstrap
    $contents = file_get_contents(base_path('bootstrap/app.php'));
    $search = '$app = new Illuminate\Foundation\Application(
    $_ENV[\'APP_BASE_PATH\'] ?? dirname(__DIR__)
);';
    $replace = '$app = new Illuminate\Foundation\Application(
    $_ENV[\'APP_BASE_PATH\'] ?? dirname(__DIR__)
);

// set the public path to this directory
$app->usePublicPath($app->basePath(\'public_html\'));';
    $contents = str_replace($search, $replace, $contents);
    file_put_contents(base_path('bootstrap/app.php'), $contents);
  }
}
