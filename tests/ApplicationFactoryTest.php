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
    public function loadProject()
    {
        $mockClient = $this->getMockBuilder(\Gitlab\Client::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(\Gitlab\Model\Project::class, $this->factory->loadProject(42, $mockClient));
    }

    /**
     * @test
     */
    public function createClient()
    {
        $this->assertInstanceOf(\Gitlab\Client::class, $this->factory->createClient('url'));
    }
}