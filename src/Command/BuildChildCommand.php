<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 05.01.17
 * Time: 15:13
 */

namespace Command;


use GitElephant\Objects\Tag;
use GitElephant\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildChildCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('build:child')
            ->addOption('name', 't', InputOption::VALUE_REQUIRED, 'the branch or tag name')
            ->setDescription('create child branch/tag');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $factory \ApplicationFactory */
        $factory = $this->getContainer()
            ->get('factory');
        $repo = $factory->createRepository(getcwd());

        $branchName = $input->getOption('name');

        if ($this->isBuildBranch($branchName)) {
            $output->writeln('<info>build branch ' . $branchName . '</info>');

            $currentBranchName = $branchName;
            if ($this->isChildBranch($currentBranchName)) {
                if ($currentBranchName == 'master') {
                    $nextName = $this->getNextMasterChild($repo);
                } else {
                    $nextName = $currentBranchName . '.' . $this->getNextBranchChild($repo, $currentBranchName);
                }
                $output->writeln('<info>create a new minor/major branch ' . $nextName . '...</info>');

                $repo->createBranch($nextName);
                $repo->push('origin', $nextName);
                $repo->checkout($currentBranchName);
            } else {
                $tagName = 'v' . $branchName . '.' . $this->getNextBranchTag($repo, $branchName);
                $output->writeln('<info>create a new release tag ' . $tagName . '...</info>');
                $repo->createTag($tagName);
                $repo->push('origin', $tagName);
            }
        } else {
            $output->writeln('<comment>branch is no minor/major branch: ' . $branchName .
                '. nothing to doo</comment>');
        }
    }

    private function getNextMasterChild(Repository $repository)
    {
        $versions = [];
        foreach ($repository->getBranches(true, true) as $branch) {
            preg_match("/[0-9]*/", $branch, $output_array);
            if ($output_array[0] == $branch) {
                $versions[intval($branch)] = $branch;
            }
        }

        return (string)count($versions);
    }

    private function getNextBranchChild(Repository $repository, $mainVersion)
    {
        $versions = [];

        foreach ($repository->getBranches(true, true) as $branch) {
            preg_match("/" . $mainVersion . ".([0-9]*)/", $branch, $output_array);
            if (count($output_array) > 0) {
                $versions[intval($output_array[1])] = $output_array[0];
            }
        }

        return (string)count($versions);
    }

    private function getNextBranchTag(Repository $repository, $branchName)
    {
        $versions = [];

        preg_match("/([0-9]*).([0-9]*)/",
            $branchName,
            $output_array);
        $mainVersion = $output_array[1];
        $secVersion = $output_array[2];

        foreach ($repository->getTags() as $tag) {
            /* @var $tag Tag */
            preg_match("/v" . $mainVersion . "." . $secVersion . ".([0-9]*)/", $tag->getName(), $output_array);
            $versions[intval($output_array[1])] = $output_array[0];
        }

        return (string)count($versions);
    }

    private function isChildBranch($branchName)
    {
        if ($branchName == 'master') {
            return true;
        } else {
            preg_match("/[0-9]*/", $branchName, $output_array);
            if ($output_array[0] === $branchName) {
                return true;
            }
        }

        return false;
    }

    private function isBuildBranch($branchName)
    {
        $buildBranch = false;

        if ($branchName == 'master') {
            $buildBranch = true;
        } else {
            preg_match("/[0-9]*[.[0-9]*]?/", $branchName, $output_array);
            if ($output_array[0] == $branchName && $branchName != '') {
                $buildBranch = true;
            }
        }

        return $buildBranch;
    }
}