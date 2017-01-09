<?php
use Command\CoverageCheckCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 09.01.17
 * Time: 11:18
 */
class CoverageCheckCommandTest extends PHPUnit_Framework_TestCase {
    /**
     * @var CoverageCheckCommand
     */
    private $command;

    /**
     * @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $containerMock;

    /**
     * @var ApplicationFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var InputInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $inputMock;
    /**
     * @var OutputInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $outputMock;

    protected function setUp () {
        parent::setUp();
        $this->command = new CoverageCheckCommand();
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->factoryMock = $this->getMockBuilder(ApplicationFactory::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->containerMock->expects($this->any())
                            ->method('get')
                            ->with('factory')
                            ->will($this->returnValue($this->factoryMock));

        $applicationMock = $this->getMockBuilder(Application::class)
                                ->setMethods(['getContainer'])
                                ->getMock();
        $applicationMock->expects($this->any())
                        ->method('getContainer')
                        ->will($this->returnValue($this->containerMock));

        $this->command->setApplication($applicationMock);

        $this->inputMock = $this->getMockBuilder(InputInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();
    }

    /**
     * @test
     */
    public function run_with_error () {
        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['clover-file', '/path/to/file'],
                                                      ['percentage', '100']]));

        $mockXml = $this->getMockBuilder(Traversable::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['xpath', 'current', 'next', 'key', 'valid', 'rewind'])
                        ->getMock();

        $mockXml->expects($this->at(0))
                ->method('xpath')
                ->with('//metrics')
                ->will($this->returnValue([['elements' => 10, 'coveredelements' => 10],
                                           ['elements' => 5, 'coveredelements' => 1]]));

        $this->factoryMock->expects($this->at(0))
                          ->method('createXmlElement')
                          ->with('/path/to/file')
                          ->will($this->returnValue($mockXml));

        $this->outputMock->expects($this->at(0))
                         ->method('writeln')
                         ->with('<error>Code coverage is 73.333333333333%, which is below the accepted 100%</error>');

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_without_error () {
        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['clover-file', '/path/to/file'],
                                                      ['percentage', '100']]));

        $mockXml = $this->getMockBuilder(Traversable::class)
                        ->disableOriginalConstructor()
                        ->setMethods(['xpath', 'current', 'next', 'key', 'valid', 'rewind'])
                        ->getMock();

        $mockXml->expects($this->at(0))
                ->method('xpath')
                ->with('//metrics')
                ->will($this->returnValue([['elements' => 10, 'coveredelements' => 10],
                                           ['elements' => 5, 'coveredelements' => 5]]));

        $this->factoryMock->expects($this->at(0))
                          ->method('createXmlElement')
                          ->with('/path/to/file')
                          ->will($this->returnValue($mockXml));

        $this->outputMock->expects($this->at(0))
                         ->method('writeln')
                         ->with('<info>Code coverage is 100% - OK</info>');

        $this->command->run($this->inputMock, $this->outputMock);
    }
}