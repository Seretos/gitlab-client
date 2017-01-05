<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 05.01.17
 * Time: 15:13
 */

namespace Command;


use GitElephant\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildChildCommand extends Command {
    protected function configure () {
        $this->setName('build:child')
             ->setDescription('create child branch/tag');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {
        $repo = new Repository(getcwd());
        try {
            $output->writeln($repo->getMainBranch());
            $output->writeln(getcwd());
            $output->writeln('hello world');
        } catch (\RuntimeException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }
}