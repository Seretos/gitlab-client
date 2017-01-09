<?php

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 06.01.17
 * Time: 12:35
 */
class ApplicationFactoryTest extends PHPUnit_Framework_TestCase {
    /**
     * @var ApplicationFactory
     */
    private $factory;

    protected function setUp () {
        parent::setUp();
        $this->factory = new ApplicationFactory();
    }

    /**
     * @test
     */
    public function loadProject () {
        /* @var $mockClient \Gitlab\Client|PHPUnit_Framework_MockObject_MockObject */
        $mockClient = $this->getMockBuilder(\Gitlab\Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->assertInstanceOf(\Gitlab\Model\Project::class, $this->factory->loadProject(42, $mockClient));
    }

    /**
     * @test
     */
    public function loadGroup () {
        /* @var $mockClient \Gitlab\Client|PHPUnit_Framework_MockObject_MockObject */
        $mockClient = $this->getMockBuilder(\Gitlab\Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->assertInstanceOf(\Gitlab\Model\Group::class, $this->factory->loadGroup(42, $mockClient));
    }

    /**
     * @test
     */
    public function createClient () {
        $this->assertInstanceOf(\Gitlab\Client::class, $this->factory->createClient('url'));
    }

    /**
     * @test
     */
    public function createXmlElement () {
        $this->assertInstanceOf(SimpleXMLElement::class, $this->factory->createXmlElement(__DIR__.'/test.xml'));
    }
}