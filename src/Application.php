<?php

use Command\BuildChildCommand;
use Command\ProtectBranchCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 05.01.17
 * Time: 14:01
 */
class Application extends BaseApplication implements ContainerAwareInterface {
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function doRun (InputInterface $input, OutputInterface $output) {
        $this->registerCommands();

        return parent::doRun($input, $output);
    }

    protected function registerCommands () {
        $this->addCommands([new BuildChildCommand(), new ProtectBranchCommand()]);
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer (ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function getContainer () {
        return $this->container;
    }
}