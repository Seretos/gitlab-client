<?php

use Command\BuildChildCommand;
use Command\CreateRepositoryCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 05.01.17
 * Time: 14:01
 */
class Application extends BaseApplication {
    /**
     * {@inheritdoc}
     */
    public function doRun (InputInterface $input, OutputInterface $output) {
        $this->registerCommands();

        return parent::doRun($input, $output);
    }

    protected function registerCommands () {
        $this->addCommands([new BuildChildCommand()]);
    }
}