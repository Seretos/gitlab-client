<?php
use Command\ReadmeReplaceCommand;
use Gitlab\Api\Projects;
use Gitlab\Api\Repositories;
use Gitlab\Client;
use Gitlab\Model\Project;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 09.01.17
 * Time: 14:34
 */
class ReadmeReplaceCommandTest extends PHPUnit_Framework_TestCase {
    /**
     * @var ReadmeReplaceCommand
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
        $this->command = new ReadmeReplaceCommand();

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
    public function run_command () {
        $clientMock = $this->getMockBuilder(Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $clientMock->expects($this->at(0))
                   ->method('authenticate')
                   ->with('my.token', Client::AUTH_URL_TOKEN);

        $projectsMock = $this->getMockBuilder(Projects::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $projectsMock->expects($this->at(0))
                     ->method('search')
                     ->with('myRepository')
                     ->will($this->returnValue([0 => ['id' => 42]]));

        $repositoriesMock = $this->getMockBuilder(Repositories::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();
        $repositoriesMock->expects($this->at(0))
                         ->method('updateFile')
                         ->with(null,
                                'README.md',
                                '
            testBundle
            ==========

            [![Build Status](https://travis-ci.org/Seretos/gitlab-client.svg?branch=testBranch)](https://travis-ci.org/Seretos/gitlab-client.svg?branch=testBranch)
            [![Coverage Status](https://coveralls.io/repos/github/Seretos/gitlab-client/badge.svg?branch=testBranch)](https://coveralls.io/github/Seretos/gitlab-client?branch=testBranch)

            your informations...
        ',
                                'testBranch',
                                'replaced the branch name')
                         ->will($this->returnValue(['file_path' => 'README.md']));

        $clientMock->expects($this->at(1))
                   ->method('api')
                   ->with('projects')
                   ->will($this->returnValue($projectsMock));
        $clientMock->expects($this->at(2))
                   ->method('api')
                   ->with('repositories')
                   ->will($this->returnValue($repositoriesMock));


        /* @var $projectMock Project|PHPUnit_Framework_MockObject_MockObject */
        $projectMock = $this->getMockBuilder(Project::class)
                            ->disableOriginalConstructor()
                            ->getMock();
        $branch = new \Gitlab\Model\Branch($projectMock, 'testBranch', $clientMock);
        $branchReflection = new ReflectionClass(\Gitlab\Model\Branch::class);
        $dataMethod = $branchReflection->getMethod('setData');
        $dataMethod->setAccessible(true);
        $dataMethod->invokeArgs($branch, ['commit', new \Gitlab\Model\Commit($projectMock, '123', $clientMock)]);

        $projectMock->expects($this->at(0))
                    ->method('branch')
                    ->with('testBranch')
                    ->will($this->returnValue($branch));
        $projectMock->expects($this->at(1))
                    ->method('getFile')
                    ->with('123', 'README.md')
                    ->will($this->returnValue(['content' => base64_encode('
            testBundle
            ==========

            [![Build Status](https://travis-ci.org/Seretos/gitlab-client.svg?branch=master)](https://travis-ci.org/Seretos/gitlab-client.svg?branch=master)
            [![Coverage Status](https://coveralls.io/repos/github/Seretos/gitlab-client/badge.svg?branch=master)](https://coveralls.io/github/Seretos/gitlab-client?branch=master)

            your informations...
        ')]));

        $this->factoryMock->expects($this->at(0))
                          ->method('createClient')
                          ->will($this->returnValue($clientMock));
        $this->factoryMock->expects($this->at(1))
                          ->method('loadProject')
                          ->with(42, $clientMock)
                          ->will($this->returnValue($projectMock));

        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['server-url', 'my.server'],
                                                      ['auth-token', 'my.token'],
                                                      ['repository', 'myRepository'],
                                                      ['branch', 'testBranch']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }
}