<?php
use Command\ProtectBranchCommand;
use Gitlab\Api\Projects;
use Gitlab\Api\Repositories;
use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: Seredos
 * Date: 07.01.2017
 * Time: 03:47
 */
class ProtectBranchCommandTest extends PHPUnit_Framework_TestCase {
    /**
     * @var ProtectBranchCommand
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
        $this->command = new ProtectBranchCommand();
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

//    /**
//     * @test
//     */
//    public function run_cant_identify () {
//        $this->inputMock->expects($this->any())
//                        ->method('getOption')
//                        ->will($this->returnValueMap([['server-url', 'http://my.domain/api/v3/']
//                                                      ,
//                                                      ['auth-token', 'myToken'],
//                                                      ['repository', 'myRepository']]));
//
//        $projectsMock = $this->getMockBuilder(Projects::class)
//                             ->disableOriginalConstructor()
//                             ->getMock();
//        $projectsMock->expects($this->at(0))
//                     ->method('show')
//                     ->with('myRepository')
//                     ->will($this->returnValue([]));
//
//        $clientMock = $this->getMockBuilder(Client::class)
//                           ->disableOriginalConstructor()
//                           ->getMock();
//        $clientMock->expects($this->at(0))
//                   ->method('authenticate')
//                   ->with('myToken', Client::AUTH_URL_TOKEN);
//
//        $clientMock->expects($this->at(1))
//                   ->method('api')
//                   ->with('projects')
//                   ->will($this->returnValue($projectsMock));
//
//        $this->factoryMock->expects($this->at(0))
//                          ->method('createClient')
//                          ->with('http://my.domain/api/v3/')
//                          ->will($this->returnValue($clientMock));
//
//        $this->outputMock->expects($this->at(0))
//                         ->method('writeln')
//                         ->with('<error>cant identify project</error>');
//
//        $this->command->run($this->inputMock, $this->outputMock);
//    }

    /**
     * @test
     */
    public function run_exception () {
        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['server-url', 'http://my.domain/api/v3/']
                                                      ,
                                                      ['auth-token', 'myToken'],
                                                      ['repository', 'myRepository']]));

        $projectsMock = $this->getMockBuilder(Projects::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $projectsMock->expects($this->at(0))
                     ->method('show')
                     ->willThrowException(new RuntimeException('myException'));

        $clientMock = $this->getMockBuilder(Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $clientMock->expects($this->at(0))
                   ->method('authenticate')
                   ->with('myToken', Client::AUTH_URL_TOKEN);

        $clientMock->expects($this->at(1))
                   ->method('api')
                   ->with('projects')
                   ->will($this->returnValue($projectsMock));

        $this->factoryMock->expects($this->at(0))
                          ->method('createClient')
                          ->with('http://my.domain/api/v3/')
                          ->will($this->returnValue($clientMock));

        $this->outputMock->expects($this->at(0))
                         ->method('writeln')
                         ->with('<error>myException</error>');

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_method () {
        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['server-url', 'http://my.domain/api/v3/']
                                                      ,
                                                      ['auth-token', 'myToken'],
                                                      ['repository', 'myRepository'],
                                                      ['branch', 'myBranch']]));

        $repositoryMock = $this->getMockBuilder(Repositories::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $repositoryMock->expects($this->at(0))
                       ->method('protectBranch')
                       ->with(42, 'myBranch');

        $projectsMock = $this->getMockBuilder(Projects::class)
                             ->disableOriginalConstructor()
                             ->getMock();
        $projectsMock->expects($this->at(0))
                     ->method('show')
                     ->with('myRepository')
                     ->will($this->returnValue(['id' => 42]));

        $clientMock = $this->getMockBuilder(Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $clientMock->expects($this->at(0))
                   ->method('authenticate')
                   ->with('myToken', Client::AUTH_URL_TOKEN);

        $clientMock->expects($this->at(1))
                   ->method('api')
                   ->with('projects')
                   ->will($this->returnValue($projectsMock));
        $clientMock->expects($this->at(2))
                   ->method('api')
                   ->with('repositories')
                   ->will($this->returnValue($repositoryMock));

        $this->factoryMock->expects($this->at(0))
                          ->method('createClient')
                          ->with('http://my.domain/api/v3/')
                          ->will($this->returnValue($clientMock));

        $this->outputMock->expects($this->at(0))
                         ->method('writeln')
                         ->with('<info>set the branch myBranch in project myRepository to protected</info>');

        $this->command->run($this->inputMock, $this->outputMock);
    }
}