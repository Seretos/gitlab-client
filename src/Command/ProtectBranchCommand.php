<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 06.01.17
 * Time: 15:35
 */

namespace Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProtectBranchCommand extends Command {
    protected function configure () {
        $this->setName('protect:branch')
             ->addOption('server-url',
                         's',
                         InputOption::VALUE_REQUIRED,
                         'the gitlab server-url. for example: http://your.domain/api/v3')
             ->addOption('auth-token', 't', InputOption::VALUE_REQUIRED, 'your gitlab user token')
             ->setDescription('set a branch protected if branch an major/minor branch');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {
    }
}