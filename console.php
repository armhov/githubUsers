<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/ConnectionClass.php';
require_once __DIR__.'/ConnectDatabaseException.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use GitHub\ConnectionClass as Connection;
use GitHub\ConnectDatabaseException;

$app = new Application('GitHub Users', '1.0.0');
$app->register('github-users')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/users');
            $users = json_decode($response->getBody()->getContents(), true);

            $connection = new Connection("localhost","root","12345","github");
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
                } else {
                    if (!$connection->crateUser($user['id'], $user['login'])) {
                        $output->writeln(sprintf("Error: Cannot create user by githubID %s.", $user['id']));
                    }
                }
            }
        } catch (ConnectDatabaseException $exception) {
            return $output->writeln($exception->getMessage());
        } catch (\Exception $exception) {
            return $output->writeln("Ooops, something went wrong!");
        }


        return $output->writeln("<info>Hello Github!</info>");
    })
    ->getApplication()
    ->run();

