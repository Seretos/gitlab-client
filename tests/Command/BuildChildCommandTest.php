<?php
use Command\BuildChildCommand;
use GitElephant\Objects\Branch;
use GitElephant\Objects\Tag;
use GitElephant\Repository;
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
    public function run_with_test_branch()
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factoryMock->expects($this->once())
            ->method('createRepository')
            ->will($this->returnValue($repositoryMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<comment>branch is no minor/major branch: testBranch. nothing to doo</comment>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', 'testBranch']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_master_branch_without_other_branches()
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('getBranches')
            ->with(true, true)
            ->will($this->returnValue(['master']));

        $this->factoryMock->expects($this->once())
            ->method('createRepository')
            ->will($this->returnValue($repositoryMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch master</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new minor/major branch 0...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', 'master']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_master_branch_with_other_branches()
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('getBranches')
            ->with(true, true)
            ->will($this->returnValue(['master', '0', '0.1', '0.2', 'testBranch']));

        $this->factoryMock->expects($this->once())
            ->method('createRepository')
            ->will($this->returnValue($repositoryMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch master</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new minor/major branch 1...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', 'master']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_major_branch_without_other_branches()
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('getBranches')
            ->with(true, true)
            ->will($this->returnValue(['master', '0', '0.1', '1', 'testBranch']));

        $this->factoryMock->expects($this->once())
            ->method('createRepository')
            ->will($this->returnValue($repositoryMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch 1</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new minor/major branch 1.0...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '1']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_major_branch_with_other_branches()
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('getBranches')
            ->with(true, true)
            ->will($this->returnValue(['master', '0', '0.1', '1', '1.0', '1.1', 'testBranch']));

        $this->factoryMock->expects($this->once())
            ->method('createRepository')
            ->will($this->returnValue($repositoryMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch 1</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new minor/major branch 1.2...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '1']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_minor_branch()
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue([]));

        $this->factoryMock->expects($this->once())
            ->method('createRepository')
            ->will($this->returnValue($repositoryMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch 0.2</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new release tag v0.2.0...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '0.2']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_minor_branch_with_other_tags()
    {
        $repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('getTags')
            ->will($this->returnValue([$this->createRepositoryTagMock('v0.2.0'),
                $this->createRepositoryTagMock('v0.2.1')]));

        $this->factoryMock->expects($this->once())
            ->method('createRepository')
            ->will($this->returnValue($repositoryMock));

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>build branch 0.2</info>');

        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>create a new release tag v0.2.2...</info>');

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnValueMap([['name', '0.2']]));

        $this->command->run($this->inputMock, $this->outputMock);
    }

    private function createRepositoryTagMock($tagName)
    {
        $mockTag = $this->getMockBuilder(Tag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockTag->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($tagName));

        return $mockTag;
    }
}