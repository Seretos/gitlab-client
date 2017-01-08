<?php

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 06.01.17
 * Time: 12:35
 */
class ApplicationFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ApplicationFactory
     */
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->factory = new ApplicationFactory();
    }

    /**
     * @test
     */
    public function createRepository()
    {
        $this->assertInstanceOf(\GitElephant\Repository::class, $this->factory->createRepository(__DIR__));
    }

    /**
     * @test
     */
    public function createClient()
    {
        $this->assertInstanceOf(\Gitlab\Client::class, $this->factory->createClient('url'));
    }
}