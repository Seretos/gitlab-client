<?php
/**
 * Created by PhpStorm.
 * User: Seredos
 * Date: 07.01.2017
 * Time: 02:21
 */

namespace Command;


use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    public function getContainer()
    {
        /* @var $app \Application */
        $app = $this->getApplication();

        return $app->getContainer();
    }
}