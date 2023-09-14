<?php

namespace Deployer;

desc('Run the build script which will build our needed assets.');
task(
    'sumo:assets:fix-node-version',
    function () {
        if (!file_exists('package.json')) {
            writeln('No package.json file found. Aborting.');
            return;
        }

        if (!shell_exec('command -v volta')) {
            writeln('Volta not found on local system.');
        }

        if (isset(json_decode(file_get_contents('package.json') ?: '', true)['volta'])) {
            return;
        }

        warning('Switching to Volta is recommended.');
        writeln('Pin the current node version with `volta pin node@$semver`');
        writeln('Where $semver can be replaced with the semantic version you want.');
        writeln('Then remove .nvmrc and commit your changes.');

        $nvmPath = trim(shell_exec('echo $HOME/.nvm/nvm.sh'));

        if (!file_exists($nvmPath)) {
            writeln('Nvm not found on local system. Aborting');
            return;
        }

        $nvmRcFile = '.nvmrc';

        // If there is no .nvmrc file, stop
        if (!file_exists($nvmRcFile)) {
            writeln('No .nvmrc file found. Aborting.');
            return;
        }

        writeln(runLocally('. ' . $nvmPath . ' && nvm install'));
    }
);

desc('Install the dependencies needed to build our assets.');
task(
    'sumo:assets:npm-install',
    function () {
        $nvmPath = trim(shell_exec('echo $HOME/.nvm/nvm.sh'));

        if (file_exists($nvmPath) && file_exists('.nvmrc')) {
            runLocally('. ' . $nvmPath . ' && nvm use && nvm exec npm install');
        } else {
            runLocally('npm install');
        }
    }
);

desc('Run the build script which will build our needed assets.');
task(
    'sumo:assets:build',
    function () {
        $nvmPath = trim(shell_exec('echo $HOME/.nvm/nvm.sh'));

        if (file_exists($nvmPath) && file_exists('.nvmrc')) {
            runLocally('. ' . $nvmPath . ' && nvm use && nvm exec npm run build');
        } else {
            runLocally('npm run build');
        }
    }
);

desc('Uploads the assets');
task(
    'sumo:assets:upload',
    function () {
        upload('public/assets', '{{release_path}}/public');
    }
);

// Specify order during deploy
after('deploy:update_code', 'sumo:assets:build');
after('sumo:assets:build', 'sumo:assets:upload');
