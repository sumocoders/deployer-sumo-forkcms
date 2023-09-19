<?php

namespace Deployer;

desc('Cleanup the codebase');
task('sumo:cleanup:remove-files', function () {
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
before('deploy:symlink', 'sumo:cleanup:remove-files');