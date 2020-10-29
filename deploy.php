<?php
namespace Deployer;

require 'recipe/drupal8.php';

set('default_timeout', 600);

// Project name
set('application', 'covid.vantuch.cz');

// Project repository
set('repository', 'git@bitbucket.org:VanTuch/covid.git');

set('http_user', 'daemon');

// Shared files/dirs between deploys
set('shared_files', [
  'web/sites/{{drupal_site}}/settings.php',
  'web/sites/{{drupal_site}}/services.yml',
  '.env'
]);

set('shared_dirs', [
  'private',
  'web/sites/{{drupal_site}}/files'
]);

set('writable_dirs', [
  'web/sites/{{drupal_site}}/files',
  'web/sites/{{drupal_site}}/files/translations'
]);

//set('writable_use_sudo', true);

set('allow_anonymous_stats', false);

set('drush', 'vendor/bin/drush');

// Hosts

host('gce')
  ->hostname('104.155.64.21')
  ->port('22')
  ->user('marek')
  ->stage('staging')
  ->set('deploy_path', '/data/docroot/dev.mujrozhlas.cz');

// Tasks
task('deploy:composer', 'composer install --no-dev --no-progress --optimize-autoloader');


// Separated to find which one fails deploy
task('deploy:drush:cr', '{{drush}} cr')->once();
task('deploy:drush:cim', '{{drush}} cim -y;')->once();
task('deploy:drush:entup', '{{drush}} entup -y;')->once();
task('deploy:drush:updb', '{{drush}} updb -y;')->once();

task('deploy:drush', [
  'deploy:drush:cr',
  'deploy:drush:updb',
  'deploy:drush:cim',
  'deploy:drush:cr',
]);

// Override the default
task('deploy', [
  'deploy:info',
  'deploy:prepare',
  'deploy:lock',
  'deploy:release',
  'deploy:update_code',
  'deploy:composer',
  'deploy:shared',
  'deploy:drush',
  'deploy:symlink',
  'deploy:unlock',
  'cleanup'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
