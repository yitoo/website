<?php

namespace Deployer;

require_once 'recipe/common.php';


set('ssh_multiplexing', true);
set('release_name', date('YmdHis'));
set('project_name', 'yitoo.io');
set('deploy_path', '/var/www/{{project_name}}');
set('release_dir', '{{deploy_path}}/releases');
set('release_path', '{{release_dir}}/{{release_name}}');
set('keep_releases', 5);

set('user', 'ssh_deployer');
set('symfony_env', 'prod');
set('repository', 'git@github.com:yitoo/website.git');

set('env', [
    'SYMFONY_ENV' => 'prod'
]);

/**
 * Symfony specifics
 */
set('build_dir', './.build');
set('build_path', '{{build_dir}}/{{release_name}}');
set('clear_paths', [
    '.env',
    '.env.dist',
    '.git',
    '.gitignore',
    'config/packages/dev',
    'config/packages/test',
    'deploy.php',
    'docker-compose.yaml',
    'docs',
    'symphony.lock',
    'README.md',
]);
set('bin_dir', 'bin');
set('var_dir', 'var');
set('shared_dirs', [ 'var/log' ]);
set('shared_files', [
    '.env',
    'public/robots.txt'
]);
set('writable_dirs', [ 'var/cache' ]);
set('bin/console', function () {
    return sprintf('{{release_path}}/%s/console', trim(get('bin_dir'), '/'));
});

set('console_options', function () {
    $options = '--no-interaction --env={{symfony_env}}';
    return get('symfony_env') !== 'prod' ? $options : sprintf('%s --no-debug', $options);
});

/**
 * Hosts
 */
host('www.yitoo.io')->stage('prod');


task('build', [
    'build:prepare',
    'build:code',
    'build:composer:install',
    'build:clear_paths',
]);

task('deploy', [
    'deploy:prepare',
    'build',
    'upload',
    'deploy:shared',
    'deploy:assets:install',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:symlink',
    'php-fpm:reload',
    'deploy:unlock',
    'cleanup'
]);

// --------------------------------------------------

task('build:prepare', function() {
   runLocally('mkdir -p {{build_dir}};');
});

task('build:code', function () {
    $repository = trim(get('repository'));
    $branch = get('branch');
    $git = 'git';
    $depth = '--depth 1';

    // If option `branch` is set.
    if (input()->hasOption('branch')) {
        $inputBranch = input()->getOption('branch');
        if (!empty($inputBranch)) {
            $branch = $inputBranch;
        }
    }

    // Branch may come from option or from configuration.
    $at = '';
    if (!empty($branch)) {
        $at = "-b $branch";
    }

    // If option `tag` is set
    if (input()->hasOption('tag')) {
        $tag = input()->getOption('tag');
        if (!empty($tag)) {
            $at = "-b $tag";
        }
    }

    // If option `tag` is not set and option `revision` is set
    if (empty($tag) && input()->hasOption('revision')) {
        $revision = input()->getOption('revision');
        if (!empty($revision)) {
            $depth = '';
        }
    }

    runLocally("$git clone $at $depth --recursive -q $repository {{build_path}} 2>&1");

    if (!empty($revision)) {
        runLocally("cd {{build_path}} && $git checkout $revision");
    }
});

task('build:composer:install', function() {
   runLocally('cd {{build_path}} && composer install --optimize-autoloader --prefer-dist --no-scripts --no-dev -vvv');
});

task('build:clear_paths', function() {
    foreach (get('clear_paths') as $path) {
        runLocally("rm -rf {{build_path}}/$path 2> /dev/null");
    }
});

task('upload', function() {
    runLocally('cd {{build_dir}} && zip -r {{release_name}}.zip {{release_name}}');
    upload('{{build_dir}}/{{release_name}}.zip', '{{release_dir}}/{{release_name}}.zip');
    run('cd {{release_dir}} && unzip -o {{release_name}}.zip && rm -f {{release_name}}.zip');
});

task('deploy:assets:install', function () {
    run('{{bin/php}} {{bin/console}} assets:install {{console_options}} {{release_path}}/public');
})->desc('Install bundle assets');

task('deploy:cache:clear', function () {
    run('{{bin/php}} {{bin/console}} cache:clear {{console_options}} --no-warmup');
})->desc('Clear cache');

task('deploy:cache:warmup', function () {
    run('{{bin/php}} {{bin/console}} cache:warmup {{console_options}}');
})->desc('Warm up cache');

task('deploy:symlink', function() {
    run('ln -sfv {{release_path}} {{deploy_path}}/current');
});

task('php-fpm:reload', function () {
    run('sudo /etc/init.d/php7.1-fpm reload');
});

task('deploy:failed', function() {
    runLocally('rm -rf {{build_dir}}/{{release_name}}');
    runLocally('rm -rf {{build_dir}}/{{release_name}}.zip');
});

task('deploy:success', function() {
    runLocally('rm -rf {{build_dir}}/{{release_name}}');
    runLocally('rm -rf {{build_dir}}/{{release_name}}.zip');
});