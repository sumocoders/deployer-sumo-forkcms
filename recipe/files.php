<?php

namespace Deployer;

desc('Replace the local files with the remote files');
task(
    'sumo:files:get',
    function () {
        $sharedDirectories = get('shared_dirs');
        if (!is_array($sharedDirectories) || empty($sharedDirectories)) {
            return;
        }

        foreach ($sharedDirectories as $directory) {
            $path = '{{deploy_path}}/shared/' . $directory;

            if (test(sprintf('[ -d %1$s ]', $path))) {
                // make sure path exists locally
                runLocally('mkdir -p ' . $directory);
                download($path, $directory. '/../');
            }
        }
    }
);

desc('Replace the remote files with the local files');
task(
    'sumo:files:put',
    function () {
        // ask for confirmation
        if (!askConfirmation('Are you sure? This will overwrite files on production!')) {
            return;
        }

        $sharedDirectories = get('shared_dirs');
        if (!is_array($sharedDirectories) || empty($sharedDirectories)) {
            return;
        }

        // remove some system dirs
        $directoriesToIgnore = [
            'var/log',      // this directory may contain useful information
            'var/sessions', // this directory may contain active sessions
        ];
        $sharedDirectories = array_values(array_filter(
            $sharedDirectories,
            function ($element) use ($directoriesToIgnore) {
                return !in_array($element, $directoriesToIgnore);
            }
        ));

        foreach ($sharedDirectories as $directory) {
            upload('./' . $directory, '{{deploy_path}}/shared/' . $directory . '/../');
        }
    }
);

desc('Cleanup the codebase');
task('sumo:files:cleanup', function () {
    run('rm -rf {{release_path}}/.github');
    run('rm -rf {{release_path}}/.git');
    run('rm -rf {{release_path}}/.gitattributes');
    run('rm -rf {{release_path}}/Dockerfile');
    run('rm -rf {{release_path}}/docker-compose.yml');
    run('rm -rf {{release_path}}/.scrutinizer.yml');
    run('rm -rf {{release_path}}/.codecov.yml');
    run('rm -rf {{release_path}}/php.ini');
    run('rm -rf {{release_path}}/phpunit.xml.dist');
    run('rm -rf {{release_path}}/phpstan.neon');
    run('rm -rf {{release_path}}/UPGRADE***');
    run('rm -rf {{release_path}}/var/docs');
    run('rm -rf {{release_path}}/tests');
    run('rm -rf {{release_path}}/var/docker');
    run('rm -rf {{release_path}}/.gitlab-ci');
    run('rm -rf {{release_path}}/.phpcs.xml.dist');
    run('rm -rf {{release_path}}/.gitlab-ci.yml');
    run('rm -rf {{release_path}}/.stylelintignore');
    run('rm -rf {{release_path}}/.stylelintrc');
    run('rm -rf {{release_path}}/.editorconfig');
    run('rm -rf {{release_path}}/.dockerignore');
    run('rm -rf {{release_path}}/CHANGELOG.nd');
});
before('deploy:symlink', 'sumo:files:cleanup');
