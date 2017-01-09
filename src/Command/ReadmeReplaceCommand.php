<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 09.01.17
 * Time: 14:13
 */

namespace Command;


use Gitlab\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadmeReplaceCommand extends BaseCommand {
    protected function configure () {
        $this->setName('replace:readme')
             ->addOption('server-url',
                         's',
                         InputOption::VALUE_REQUIRED,
                         'the gitlab server-url. for example: http://your.domain/api/v3/')
             ->addOption('auth-token', 't', InputOption::VALUE_REQUIRED, 'your gitlab user token')
             ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'the repository name to protect')
             ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'the branch name to protect')
             ->setDescription('replace the branch name in the readme file');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {
        /* @var $factory \ApplicationFactory */
        $factory = $this->getContainer()
                        ->get('factory');

        $client = $factory->createClient($input->getOption('server-url'));
        $client->authenticate($input->getOption('auth-token'), Client::AUTH_URL_TOKEN);

        $projectJson = $client->api('projects')
                              ->search($input->getOption('repository'));
        $project = $factory->loadProject($projectJson[0]['id'], $client);
        $branch = $project->branch($input->getOption('branch'));

        $file = $project->getFile($branch->commit->id, 'README.md');
        $readmeContent = base64_decode($file['content']);

        $readmeContent = preg_replace('/\?branch\=([\d\w.-]*)/',
                                      '?branch='.$input->getOption('branch'),
                                      $readmeContent);

        $branch->updateFile('README.md', $readmeContent, 'replaced the branch name');
    }
}