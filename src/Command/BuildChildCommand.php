<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 05.01.17
 * Time: 15:13
 */

namespace Command;

use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Gitlab\Model\Branch;
use Gitlab\Model\Tag;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildChildCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('build:child')
            ->addOption('server-url',
                's',
                InputOption::VALUE_REQUIRED,
                'the gitlab server-url. for example: http://your.domain/api/v3/')
            ->addOption('auth-token', 't', InputOption::VALUE_REQUIRED, 'your gitlab user token')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'the repository name')
            ->addOption('branch', 'b', InputOption::VALUE_REQUIRED, 'the branch or tag name')
            ->setDescription('create child branch/tag');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $factory \ApplicationFactory */
        $factory = $this->getContainer()
            ->get('factory');

        $client = $factory->createClient($input->getOption('server-url'));
        $client->authenticate($input->getOption('auth-token'), Client::AUTH_URL_TOKEN);

        $branchName = $input->getOption('branch');

        try {
            $projectJson = $client->api('projects')->search($input->getOption('repository'));
            if (count($projectJson) != 1) {
                throw new RuntimeException('cant identify project');
            }

            $project = $factory->loadProject($projectJson[0]['id'], $client);
            $branches = $project->branches();
            $tags = $project->tags();

            if ($this->isBuildBranch($branchName)) {
                $output->writeln('<info>build branch ' . $branchName . '</info>');
                if ($this->isChildBranch($branchName)) {
                    if ($branchName == 'master') {
                        $nextName = $this->getNextMasterChild($branches);
                    } else {
                        $nextName = $branchName . '.' . $this->getNextBranchChild($branches, $branchName);
                    }
                    $output->writeln('<info>create a new minor/major branch ' . $nextName . '...</info>');
                    $project->createBranch($nextName, $branchName);
                } else {
                    $tagName = 'v' . $branchName . '.' . $this->getNextBranchTag($tags, $branchName);
                    $output->writeln('<info>create a new release tag ' . $tagName . '...</info>');
                    $client->api('repositories')->createTag($projectJson[0]['id'], $tagName, $branchName);
                }
            } else {
                $output->writeln('<info>branch is no minor/major branch: ' . $branchName . '. nothing to doo</info>');
            }
        } catch (RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param Branch[] $branches
     * @return string
     */
    private function getNextMasterChild(array $branches)
    {
        $versions = [];
        foreach ($branches as $branch) {
            preg_match("/[0-9]*/", $branch->name, $output_array);
            if ($output_array[0] == $branch->name) {
                $versions[intval($branch->name)] = $branch->name;
            }
        }

        return (string)count($versions);
    }

    /**
     * @param Branch[] $branches
     * @param $mainVersion
     * @return string
     */
    private function getNextBranchChild(array $branches, $mainVersion)
    {
        $versions = [];

        foreach ($branches as $branch) {
            preg_match("/" . $mainVersion . ".([0-9]*)/", $branch->name, $output_array);
            if (count($output_array) > 0) {
                $versions[intval($output_array[1])] = $output_array[0];
            }
        }

        return (string)count($versions);
    }

    /**
     * @param Tag[] $tags
     * @param $branchName
     * @return string
     */
    private function getNextBranchTag(array $tags, $branchName)
    {
        $versions = [];

        preg_match("/([0-9]*).([0-9]*)/",
            $branchName,
            $output_array);
        $mainVersion = $output_array[1];
        $secVersion = $output_array[2];

        foreach ($tags as $tag) {
            preg_match("/v" . $mainVersion . "." . $secVersion . ".([0-9]*)/", $tag->name, $output_array);
            if (count($output_array) > 1) {
                $versions[intval($output_array[1])] = $output_array[0];
            }
        }

        return (string)count($versions);
    }

    private function isChildBranch($branchName)
    {
        if ($branchName == 'master') {
            return true;
        } else {
            preg_match("/^[0-9]*$/", $branchName, $output_array);
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
            preg_match("/^[0-9]*[.[0-9]*]?$/", $branchName, $output_array);
            if ($output_array[0] == $branchName && $branchName != '') {
                $buildBranch = true;
            }
        }

        return $buildBranch;
    }
}
