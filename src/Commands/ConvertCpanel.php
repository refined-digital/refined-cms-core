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

    // webpack.mix
    $contents = file_get_contents(base_path('webpack.mix.js'));
    $search = ['public/', '.disableNotifications()'];
    $replace = ['public_html/', '.disableNotifications()
  .setPublicPath(\'public_html/\')'];
    $contents = str_replace($search, $replace, $contents);
    file_put_contents(base_path('webpack.mix.js'), $contents);


    // server
    $contents = file_get_contents(base_path('server.php'));
    $contents = str_replace('/public', '/public_html', $contents);
    file_put_contents(base_path('server.php'), $contents);

    // public html
    $contents = file_get_contents(public_path('index.php'));
    $search = '$app = require_once __DIR__.\'/../bootstrap/app.php\';';
    $replace = '$app = require_once __DIR__.\'/../bootstrap/app.php\';

// set the public path to this directory
$app->bind(\'path.public\', function() {
    return __DIR__;
});';
    $contents = str_replace($search, $replace, $contents);
    file_put_contents(public_path('index.php'), $contents);


    // bootstrap
    $contents = file_get_contents(base_path('bootstrap/app.php'));
    $search = '$app = new Illuminate\Foundation\Application(
    $_ENV[\'APP_BASE_PATH\'] ?? dirname(__DIR__)
);';
    $replace = '$app = new Illuminate\Foundation\Application(
    $_ENV[\'APP_BASE_PATH\'] ?? dirname(__DIR__)
);

// set the public path to this directory
$app->bind(\'path.public\', function() {
    $dir = realpath(__DIR__ . \'/../public_html\');
    return $dir;
});';
    $contents = str_replace($search, $replace, $contents);
    file_put_contents(base_path('bootstrap/app.php'), $contents);
  }
}
