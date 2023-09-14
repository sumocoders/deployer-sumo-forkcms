<?php

namespace Deployer;

use TijsVerkoyen\DeployerSumo\Utility\Database;
use TijsVerkoyen\DeployerSumo\Utility\Configuration;

$databaseUtility = new Database();

desc('Create the staging database if it does not exists yet');
task(
    'sumo:db:create',
    function () use ($databaseUtility) {
        writeln(
            run('create_db ' . $databaseUtility->getName())
        );
    }
)->select('stage=staging');

desc('Create the local database if it does not exists yet');
task(
    'sumo:db:create-local',
    function () use ($databaseUtility) {
        $localHost = Configuration::fromLocal()->get('FORK_DATABASE_HOST');
        $localPort = Configuration::fromLocal()->get('FORK_DATABASE_PORT');
        $localName = Configuration::fromLocal()->get('FORK_DATABASE_NAME');
        $localUser = Configuration::fromLocal()->get('FORK_DATABASE_USER');
        $localPassword = Configuration::fromLocal()->get('FORK_DATABASE_PASSWORD');

        $localDatabaseUrl = parse_url("mysql://{$localUser}:{$localPassword}@{$localHost}:{$localPort}/{$localName}?serverVersion=5.7&charset=utf8mb4");

        runLocally(
            sprintf(
                'mysql %1$s -e "CREATE DATABASE IF NOT EXISTS %2$s;"',
                $databaseUtility->getConnectionOptions($localDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatabaseUrl)
            )
        );
    }
);

desc('Get info about the database');
task(
    'sumo:db:info',
    function () use ($databaseUtility) {
        writeln(
            run('info_db ' . $databaseUtility->getName())
        );
    }
)->select('stage=staging');

desc('Replace the local database with the remote database');
task(
    'sumo:db:get',
    function () use ($databaseUtility) {
        $remoteHost = Configuration::fromRemote()->get('FORK_DATABASE_HOST');
        $remotePort = Configuration::fromRemote()->get('FORK_DATABASE_PORT');
        $remoteName = Configuration::fromRemote()->get('FORK_DATABASE_NAME');
        $remoteUser = Configuration::fromRemote()->get('FORK_DATABASE_USER');
        $remotePassword = Configuration::fromRemote()->get('FORK_DATABASE_PASSWORD');

        $localHost = Configuration::fromLocal()->get('FORK_DATABASE_HOST');
        $localPort = Configuration::fromLocal()->get('FORK_DATABASE_PORT');
        $localName = Configuration::fromLocal()->get('FORK_DATABASE_NAME');
        $localUser = Configuration::fromLocal()->get('FORK_DATABASE_USER');
        $localPassword = Configuration::fromLocal()->get('FORK_DATABASE_PASSWORD');

        $remoteDatabaseUrl = parse_url("mysql://{$remoteUser}:{$remotePassword}@{$remoteHost}:{$remotePort}/{$remoteName}?serverVersion=5.7&charset=utf8mb4");
        $localDatabaseUrl = parse_url("mysql://{$localUser}:{$localPassword}@{$localHost}:{$localPort}/{$localName}?serverVersion=5.7&charset=utf8mb4");

        run(
            sprintf(
                'mysqldump --lock-tables=false --set-charset %1$s %2$s > {{deploy_path}}/db_download.tmp.sql',
                $databaseUtility->getConnectionOptions($remoteDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($remoteDatabaseUrl)
            )
        );
        download(
            '{{deploy_path}}/db_download.tmp.sql',
            './db_download.tmp.sql'
        );
        run('rm {{deploy_path}}/db_download.tmp.sql');

        runLocally(
            sprintf(
                'mysql %1$s %2$s < ./db_download.tmp.sql',
                $databaseUtility->getConnectionOptions($localDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatabaseUrl)
            )
        );
        runLocally('rm ./db_download.tmp.sql');
    }
);

desc('Replace the remote database with the local database');
task(
    'sumo:db:put',
    function () use ($databaseUtility) {
        $remoteHost = Configuration::fromRemote()->get('FORK_DATABASE_HOST');
        $remotePort = Configuration::fromRemote()->get('FORK_DATABASE_PORT');
        $remoteName = Configuration::fromRemote()->get('FORK_DATABASE_NAME');
        $remoteUser = Configuration::fromRemote()->get('FORK_DATABASE_USER');
        $remotePassword = Configuration::fromRemote()->get('FORK_DATABASE_PASSWORD');

        $localHost = Configuration::fromLocal()->get('FORK_DATABASE_HOST');
        $localPort = Configuration::fromLocal()->get('FORK_DATABASE_PORT');
        $localName = Configuration::fromLocal()->get('FORK_DATABASE_NAME');
        $localUser = Configuration::fromLocal()->get('FORK_DATABASE_USER');
        $localPassword = Configuration::fromLocal()->get('FORK_DATABASE_PASSWORD');

        $remoteDatabaseUrl = parse_url("mysql://{$remoteUser}:{$remotePassword}@{$remoteHost}:{$remotePort}/{$remoteName}?serverVersion=5.7&charset=utf8mb4");
        $localDatabaseUrl = parse_url("mysql://{$localUser}:{$localPassword}@{$localHost}:{$localPort}/{$localName}?serverVersion=5.7&charset=utf8mb4");

        // create a backup
        // @todo make separate backup dir
        run(
            sprintf(
                'mysqldump --lock-tables=false --set-charset %1$s %2$s > {{deploy_path}}/backup_%3$s.sql',
                $databaseUtility->getConnectionOptions($remoteDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($remoteDatabaseUrl),
                date('YmdHi')
            )
        );

        runLocally(
            sprintf(
                'mysqldump --column-statistics=0 --lock-tables=false --set-charset %1$s %2$s > ./db_upload.tmp.sql',
                $databaseUtility->getConnectionOptions($localDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($localDatabaseUrl)
            )
        );

        upload('./db_upload.tmp.sql', '{{deploy_path}}/db_upload.tmp.sql');
        runLocally('rm ./db_upload.tmp.sql');

        run(
            sprintf(
                'mysql %1$s %2$s < {{deploy_path}}/db_upload.tmp.sql',
                $databaseUtility->getConnectionOptions($remoteDatabaseUrl),
                $databaseUtility->getNameFromConnectionOptions($remoteDatabaseUrl)
            )
        );
        run('rm {{deploy_path}}/db_upload.tmp.sql');
    }
);
