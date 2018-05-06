<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/ConnectionClass.php';
require_once __DIR__.'/ConnectDatabaseException.php';

use GitHub\ConnectDatabaseException;
use GitHub\ConnectionClass as Connection;
use GuzzleHttp\Client;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

$app = new Application('GitHub Users', '1.0.0');
$app->register('github-users')
    ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Database Host')
    ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Database User')
    ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Database Password')
    ->addOption('database', null, InputOption::VALUE_REQUIRED, 'Database Name')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/users');
            $users = json_decode($response->getBody()->getContents(), true);

            $connection = new Connection($input->getOption('host'),$input->getOption('user'),$input->getOption('password'),$input->getOption('database'));
            if (!$connection->createUserTable()) {
                $output->writeln("Warning: `user` table already exists.");
            }

            foreach ($users as $user) {
                if (!isset($user['id'], $user['login'])) {
                    continue;
                }

                if ($connection->findUser($user['id'])) {
                    if (!$connection->updateUser($user['id'], $user['login'])) {
                        $output->writeln(sprintf("Error: Cannot update user by githubID %s.", $user['id']));
                    }
                } elseif (!$connection->crateUser($user['id'], $user['login'])) {
                    $output->writeln(sprintf("Error: Cannot create user by githubID %s.", $user['id']));
                }
            }
        } catch (ConnectDatabaseException $exception) {
            return $output->writeln($exception->getMessage());
        } catch (\Exception $exception) {
            return $output->writeln("Ooops, something went wrong!");
        }


        return $output->writeln("<info>Github users successfully added!</info>");
    })
    ->getApplication()
    ->run();
