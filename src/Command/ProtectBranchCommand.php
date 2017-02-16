<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 06.01.17
 * Time: 15:35
 */

namespace Command;


use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProtectBranchCommand extends BaseCommand {
    protected function configure () {
        $this->setName('protect:branch')
             ->addOption('server-url',
                         's',
                         InputOption::VALUE_REQUIRED,
                         'the gitlab server-url. for example: http://your.domain/api/v3/')
             ->addOption('auth-token', 't', InputOption::VALUE_REQUIRED, 'your gitlab user token')
             ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'the repository name to protect')
             ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'the branch name to protect')
             ->setDescription('set a branch protected');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {
        /* @var $factory \ApplicationFactory */
        $factory = $this->getContainer()
                        ->get('factory');

        $client = $factory->createClient($input->getOption('server-url'));
        $client->authenticate($input->getOption('auth-token'), Client::AUTH_URL_TOKEN);

        try {
            $project = $client->api('projects')
                              ->show($input->getOption('repository'));
//            var_dump($project);
//            if (count($project) != 1) {
//                throw new RuntimeException('cant identify project');
//            }
            $output->writeln('<info>set the branch '.$input->getOption('branch').' in project '.
                             $input->getOption('repository').' to protected</info>');
            $client->api('repositories')
                   ->protectBranch($project['id'], $input->getOption('branch'));
        } catch (RuntimeException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}