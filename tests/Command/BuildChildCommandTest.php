<?php
use Command\BuildChildCommand;
use GitElephant\Objects\Branch;
use GitElephant\Objects\Tag;
use GitElephant\Repository;
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
 * Date: 06.01.17
 * Time: 12:39
 */
class BuildChildCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BuildChildCommand
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

    protected function setUp()
    {
        parent::setUp();
        $this->command = new BuildChildCommand();

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
    public function run_with_invalid_repository()
    {
        $projectsMock = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projectsMock->expects($this->at(0))->method('search')->with('myRepository')->will($this->returnValue([]));


        $clientMock = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->expects($this->at(0))->method('authenticate')->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))->method('api')->with('projects')->will($this->returnValue($projectsMock));

        $this->factoryMock->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($clientMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<error>cant identify project</error>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['server-url', 'my.server'], ['auth-token', 'my.token'], ['repository', 'myRepository'], ['branch', 'testBranch']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_test_branch()
    {
        $projectsMock = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projectsMock->expects($this->at(0))->method('search')->with('myRepository')->will($this->returnValue([0 => ['id' => 42]]));

        $repositoriesMock = $this->getMockBuilder(Repositories::class)->disableOriginalConstructor()->getMock();
        $repositoriesMock->expects($this->at(0))->method('branches')->with(42)->will($this->returnValue([]));
        $repositoriesMock->expects($this->at(1))->method('tags')->with(42)->will($this->returnValue([]));

        $clientMock = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->expects($this->at(0))->method('authenticate')->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))->method('api')->with('projects')->will($this->returnValue($projectsMock));
        $clientMock->expects($this->at(2))->method('api')->with('repo')->will($this->returnValue($repositoriesMock));
        $clientMock->expects($this->at(3))->method('api')->with('repo')->will($this->returnValue($repositoriesMock));

        $this->factoryMock->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($clientMock));

        $this->factoryMock->expects($this->once())->method('loadProject')->with(42, $clientMock)->will($this->returnValue(new Project(42, $clientMock)));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>branch is no minor/major branch: testBranch. nothing to doo</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['server-url', 'my.server'], ['auth-token', 'my.token'], ['repository', 'myRepository'], ['branch', 'testBranch']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_master_branch()
    {
        $projectsMock = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projectsMock->expects($this->at(0))->method('search')->with('myRepository')->will($this->returnValue([0 => ['id' => 42]]));

        $repositoriesMock = $this->getMockBuilder(Repositories::class)->disableOriginalConstructor()->getMock();
        $repositoriesMock->expects($this->at(0))->method('branches')->with(42)->will($this->returnValue([]));
        $repositoriesMock->expects($this->at(1))->method('tags')->with(42)->will($this->returnValue([]));
        $repositoriesMock->expects($this->at(2))->method('createBranch')->with(42, '0', 'master')->will($this->returnValue(['name' => '0']));

        $clientMock = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->expects($this->at(0))->method('authenticate')->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))->method('api')->with('projects')->will($this->returnValue($projectsMock));
        $clientMock->expects($this->at(2))->method('api')->with('repo')->will($this->returnValue($repositoriesMock));
        $clientMock->expects($this->at(3))->method('api')->with('repo')->will($this->returnValue($repositoriesMock));
        $clientMock->expects($this->at(4))->method('api')->with('repositories')->will($this->returnValue($repositoriesMock));

        $this->factoryMock->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($clientMock));

        $this->factoryMock->expects($this->once())->method('loadProject')->with(42, $clientMock)->will($this->returnValue(new Project(42, $clientMock)));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch master</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new minor/major branch 0...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['server-url', 'my.server'], ['auth-token', 'my.token'], ['repository', 'myRepository'], ['branch', 'master']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_master_branch_and_other()
    {
        $clientMock = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $projectsMock = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projectsMock->expects($this->at(0))->method('search')->with('myRepository')->will($this->returnValue([0 => ['id' => 42]]));

        $projectMock = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $branch1 = new \Gitlab\Model\Branch($projectMock, '0', $clientMock);
        $branch2 = new \Gitlab\Model\Branch($projectMock, 'test1', $clientMock);
        $projectMock->expects($this->at(0))->method('branches')->will($this->returnValue([$branch1, $branch2]));

        $clientMock->expects($this->at(0))->method('authenticate')->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))->method('api')->with('projects')->will($this->returnValue($projectsMock));

        $this->factoryMock->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($clientMock));

        $this->factoryMock->expects($this->once())->method('loadProject')->with(42, $clientMock)->will($this->returnValue($projectMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch master</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new minor/major branch 1...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['server-url', 'my.server'], ['auth-token', 'my.token'], ['repository', 'myRepository'], ['branch', 'master']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_major_branch()
    {
        $clientMock = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $projectsMock = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projectsMock->expects($this->at(0))->method('search')->with('myRepository')->will($this->returnValue([0 => ['id' => 42]]));

        $projectMock = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $branch1 = new \Gitlab\Model\Branch($projectMock, '0', $clientMock);
        $branch2 = new \Gitlab\Model\Branch($projectMock, 'test1', $clientMock);
        $branch3 = new \Gitlab\Model\Branch($projectMock, '1', $clientMock);
        $branch4 = new \Gitlab\Model\Branch($projectMock, '0.0', $clientMock);
        $projectMock->expects($this->at(0))->method('branches')->will($this->returnValue([$branch1, $branch2, $branch3, $branch4]));

        $clientMock->expects($this->at(0))->method('authenticate')->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))->method('api')->with('projects')->will($this->returnValue($projectsMock));

        $this->factoryMock->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($clientMock));

        $this->factoryMock->expects($this->once())->method('loadProject')->with(42, $clientMock)->will($this->returnValue($projectMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch 0</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new minor/major branch 0.1...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['server-url', 'my.server'], ['auth-token', 'my.token'], ['repository', 'myRepository'], ['branch', '0']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_minor_branch()
    {
        $clientMock = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $projectsMock = $this->getMockBuilder(Projects::class)->disableOriginalConstructor()->getMock();
        $projectsMock->expects($this->at(0))->method('search')->with('myRepository')->will($this->returnValue([0 => ['id' => 42]]));

        $projectMock = $this->getMockBuilder(Project::class)->disableOriginalConstructor()->getMock();

        $branch1 = new \Gitlab\Model\Branch($projectMock, '0', $clientMock);
        $branch2 = new \Gitlab\Model\Branch($projectMock, 'test1', $clientMock);
        $branch3 = new \Gitlab\Model\Branch($projectMock, '1', $clientMock);
        $branch4 = new \Gitlab\Model\Branch($projectMock, '0.0', $clientMock);
        $branch5 = new \Gitlab\Model\Branch($projectMock, '0.1', $clientMock);
        $projectMock->expects($this->at(0))->method('branches')->will($this->returnValue([$branch1, $branch2, $branch3, $branch4, $branch5]));

        $tag1 = new \Gitlab\Model\Tag($projectMock, 'v0.0.0', $clientMock);
        $tag2 = new \Gitlab\Model\Tag($projectMock, 'v0.0.1', $clientMock);
        $tag3 = new \Gitlab\Model\Tag($projectMock, 'v0.1.0', $clientMock);
        $projectMock->expects($this->at(1))->method('tags')->will($this->returnValue([$tag1, $tag2, $tag3]));

        $repositoriesMock = $this->getMockBuilder(Repositories::class)->disableOriginalConstructor()->getMock();
        $repositoriesMock->expects($this->at(0))->method('createTag')->with(42, 'v0.1.1', '0.1');

        $clientMock->expects($this->at(0))->method('authenticate')->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))->method('api')->with('projects')->will($this->returnValue($projectsMock));
        $clientMock->expects($this->at(2))->method('api')->with('repositories')->will($this->returnValue($repositoriesMock));

        $this->factoryMock->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($clientMock));

        $this->factoryMock->expects($this->once())->method('loadProject')->with(42, $clientMock)->will($this->returnValue($projectMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch 0.1</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new release tag v0.1.1...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['server-url', 'my.server'], ['auth-token', 'my.token'], ['repository', 'myRepository'], ['branch', '0.1']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }
//
//    /**
//     * @test
//     */
//    public function run_with_master_branch_without_other_branches()
//    {
//        $repositoryMock = $this->getMockBuilder(Repository::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $repositoryMock->expects($this->once())
//            ->method('getBranches')
//            ->with(true, true)
//            ->will($this->returnValue(['master']));
//
//        $this->factoryMock->expects($this->once())
//            ->method('createRepository')
//            ->will($this->returnValue($repositoryMock));
//
//        $this->outputMock->expects($this->at(0))
//            ->method('writeln')
//            ->with('<info>build branch master</info>');
//
//        $this->outputMock->expects($this->at(1))
//            ->method('writeln')
//            ->with('<info>create a new minor/major branch 0...</info>');
//
//        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', 'master']]));
//
//        $this->command->run($this->inputMock, $this->outputMock);
//    }
//
//    /**
//     * @test
//     */
//    public function run_with_master_branch_with_other_branches()
//    {
//        $repositoryMock = $this->getMockBuilder(Repository::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $repositoryMock->expects($this->once())
//            ->method('getBranches')
//            ->with(true, true)
//            ->will($this->returnValue(['master', '0', '0.1', '0.2', 'testBranch']));
//
//        $this->factoryMock->expects($this->once())
//            ->method('createRepository')
//            ->will($this->returnValue($repositoryMock));
//
//        $this->outputMock->expects($this->at(0))
//            ->method('writeln')
//            ->with('<info>build branch master</info>');
//
//        $this->outputMock->expects($this->at(1))
//            ->method('writeln')
//            ->with('<info>create a new minor/major branch 1...</info>');
//
//        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', 'master']]));
//
//        $this->command->run($this->inputMock, $this->outputMock);
//    }
//
//    /**
//     * @test
//     */
//    public function run_with_major_branch_without_other_branches()
//    {
//        $repositoryMock = $this->getMockBuilder(Repository::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $repositoryMock->expects($this->once())
//            ->method('getBranches')
//            ->with(true, true)
//            ->will($this->returnValue(['master', '0', '0.1', '1', 'testBranch']));
//
//        $this->factoryMock->expects($this->once())
//            ->method('createRepository')
//            ->will($this->returnValue($repositoryMock));
//
//        $this->outputMock->expects($this->at(0))
//            ->method('writeln')
//            ->with('<info>build branch 1</info>');
//
//        $this->outputMock->expects($this->at(1))
//            ->method('writeln')
//            ->with('<info>create a new minor/major branch 1.0...</info>');
//
//        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '1']]));
//
//        $this->command->run($this->inputMock, $this->outputMock);
//    }
//
//    /**
//     * @test
//     */
//    public function run_with_major_branch_with_other_branches()
//    {
//        $repositoryMock = $this->getMockBuilder(Repository::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $repositoryMock->expects($this->once())
//            ->method('getBranches')
//            ->with(true, true)
//            ->will($this->returnValue(['master', '0', '0.1', '1', '1.0', '1.1', 'testBranch']));
//
//        $this->factoryMock->expects($this->once())
//            ->method('createRepository')
//            ->will($this->returnValue($repositoryMock));
//
//        $this->outputMock->expects($this->at(0))
//            ->method('writeln')
//            ->with('<info>build branch 1</info>');
//
//        $this->outputMock->expects($this->at(1))
//            ->method('writeln')
//            ->with('<info>create a new minor/major branch 1.2...</info>');
//
//        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '1']]));
//
//        $this->command->run($this->inputMock, $this->outputMock);
//    }
//
//    /**
//     * @test
//     */
//    public function run_with_minor_branch()
//    {
//        $repositoryMock = $this->getMockBuilder(Repository::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $repositoryMock->expects($this->once())
//            ->method('getTags')
//            ->will($this->returnValue([]));
//
//        $this->factoryMock->expects($this->once())
//            ->method('createRepository')
//            ->will($this->returnValue($repositoryMock));
//
//        $this->outputMock->expects($this->at(0))
//            ->method('writeln')
//            ->with('<info>build branch 0.2</info>');
//
//        $this->outputMock->expects($this->at(1))
//            ->method('writeln')
//            ->with('<info>create a new release tag v0.2.0...</info>');
//
//        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '0.2']]));
//
//        $this->command->run($this->inputMock, $this->outputMock);
//    }
//
//    /**
//     * @test
//     */
//    public function run_with_minor_branch_with_other_tags()
//    {
//        $repositoryMock = $this->getMockBuilder(Repository::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $repositoryMock->expects($this->once())
//            ->method('getTags')
//            ->will($this->returnValue([$this->createRepositoryTagMock('v0.2.0'),
//                $this->createRepositoryTagMock('v0.2.1')]));
//
//        $this->factoryMock->expects($this->once())
//            ->method('createRepository')
//            ->will($this->returnValue($repositoryMock));
//
//        $this->outputMock->expects($this->at(0))
//            ->method('writeln')
//            ->with('<info>build branch 0.2</info>');
//
//        $this->outputMock->expects($this->at(1))
//            ->method('writeln')
//            ->with('<info>create a new release tag v0.2.2...</info>');
//
//        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '0.2']]));
//
//        $this->command->run($this->inputMock, $this->outputMock);
//    }
//
//    private function createRepositoryTagMock($tagName)
//    {
//        $mockTag = $this->getMockBuilder(Tag::class)
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $mockTag->expects($this->any())
//            ->method('getName')
//            ->will($this->returnValue($tagName));
//
//        return $mockTag;
//    }
}