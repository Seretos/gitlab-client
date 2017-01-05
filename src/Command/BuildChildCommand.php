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
            $branch = $repo->getMainBranch();

            if ($this->isBuildBranch($branch->getName())) {
                $output->writeln('<info>build branch '.$branch->getName().'</info>');

                if ($this->isChildBranch($branch->getName())) {
                    $nextName = '';
                    if ($branch->getName() == 'master') {
                        $nextName = $this->getNextMasterChild($repo);
                    }
                    $output->writeln('<info>create a new build branch '.$nextName.'...</info>');

                    $repo->createBranch($nextName);
                    $repo->checkout($nextName);
                    $repo->push($nextName);
                } else {
                    $output->writeln('<info>create a new build tag</info>');
                }
            } else {
                $output->writeln('<comment>branch is no build branch: '.$branch->getName().
                                 '. nothing to doo</comment>');
            }
        } catch (\RuntimeException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }

    private function getNextMasterChild (Repository $repository) {
        $versions = [];
        foreach ($repository->getBranches(true, true) as $branch) {
            preg_match("/[0-9]*/", $branch, $output_array);
            if ($output_array[0] == $branch) {
                $versions[intval($branch)] = $branch;
            }
        }

        return (string) count($versions);
    }

//    private function getNextBranchChild (Repository $repository) {
//        $versions = [];
//        $mainVersion = $repository->getMainBranch()
//                                  ->getName();
//
//        foreach ($repository->getBranches(true, true) as $branch) {
//            preg_match("/".$mainVersion.".([0-9]*)/", $branch, $output_array);
//        }
//    }

    private function isChildBranch ($branchName) {
        if ($branchName == 'master') {
            return true;
        } else {
            preg_match("/[0-9]*/", $branchName, $output_array);
            if ($output_array[0] == $branchName) {
                return true;
            }
        }

        return false;
    }

    private function isBuildBranch ($branchName) {
        $buildBranch = false;

        if ($branchName == 'master') {
            $buildBranch = true;
        } else {
            preg_match("/[0-9]*[.[0-9]*]?/", $branchName, $output_array);
            if ($output_array[0] == $branchName) {
                $buildBranch = true;
            }
        }

        return $buildBranch;
    }
}