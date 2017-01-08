<?php
use GitElephant\Repository;
use Gitlab\Client;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 06.01.17
 * Time: 12:29
 */
class ApplicationFactory
{
    public function createRepository($path)
    {
        return new Repository($path, new \GitElephant\GitBinary('git'));
    }

    public function createClient($url)
    {
        return new Client($url);
    }
}