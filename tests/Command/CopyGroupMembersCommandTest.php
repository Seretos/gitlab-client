<?php
use Command\CopyGroupMembersCommand;
use Gitlab\Api\Groups;
use Gitlab\Client;
use Gitlab\Model\Group;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 09.01.17
 * Time: 13:01
 */
class CopyGroupMembersCommandTest extends PHPUnit_Framework_TestCase {
    /**
     * @var CopyGroupMembersCommand
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
        $this->command = new CopyGroupMembersCommand();

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
    public function run_without_members () {
        $groupsMock = $this->getMockBuilder(Groups::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $groupsMock->expects($this->at(0))
                   ->method('search')
                   ->with('mySourceGroup')
                   ->will($this->returnValue([0 => ['id' => 42]]));
        $groupsMock->expects($this->at(1))
                   ->method('search')
                   ->with('myDestGroup')
                   ->will($this->returnValue([0 => ['id' => 43]]));
        $groupsMock->expects($this->at(2))
                   ->method('members')
                   ->with(42)
                   ->will($this->returnValue([]));
        $groupsMock->expects($this->at(3))
                   ->method('members')
                   ->with(43)
                   ->will($this->returnValue([]));

        $clientMock = $this->getMockBuilder(Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $clientMock->expects($this->at(0))
                   ->method('authenticate')
                   ->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(2))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(3))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(4))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));

        $group1 = new Group(42, $clientMock);
        $group2 = new Group(43, $clientMock);


        $this->factoryMock->expects($this->at(0))
                          ->method('createClient')
                          ->will($this->returnValue($clientMock));
        $this->factoryMock->expects($this->at(1))
                          ->method('loadGroup')
                          ->with(42, $clientMock)
                          ->will($this->returnValue($group1));
        $this->factoryMock->expects($this->at(2))
                          ->method('loadGroup')
                          ->with(43, $clientMock)
                          ->will($this->returnValue($group2));

        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['server-url', 'my.server'],
                                                      ['auth-token', 'my.token'],
                                                      ['source-group', 'mySourceGroup'],
                                                      ['destination-group', 'myDestGroup']]));

        $this->outputMock->expects($this->at(0))
                         ->method('writeln')
                         ->with('<info>no members in source group!</info>');

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_members () {
        $clientMock = $this->getMockBuilder(Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $groupsMock = $this->getMockBuilder(Groups::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $groupsMock->expects($this->at(0))
                   ->method('search')
                   ->with('mySourceGroup')
                   ->will($this->returnValue([0 => ['id' => 42, 'name' => 'mySourceGroup']]));
        $groupsMock->expects($this->at(1))
                   ->method('search')
                   ->with('myDestGroup')
                   ->will($this->returnValue([0 => ['id' => 43, 'name' => 'myDestGroup']]));
        $groupsMock->expects($this->at(2))
                   ->method('members')
                   ->with(42)
                   ->will($this->returnValue([['id' => 1, 'username' => 'user1', 'access_level' => 10],
                                              ['id' => 2, 'username' => 'user2', 'access_level' => 20]]));
        $groupsMock->expects($this->at(3))
                   ->method('members')
                   ->with(43)
                   ->will($this->returnValue([]));
        $groupsMock->expects($this->at(4))
                   ->method('addMember')
                   ->with(43, 1, 10)
                   ->will($this->returnValue([]));
        $groupsMock->expects($this->at(5))
                   ->method('addMember')
                   ->with(43, 2, 20)
                   ->will($this->returnValue([]));

        $clientMock->expects($this->at(0))
                   ->method('authenticate')
                   ->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(2))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(3))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(4))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(5))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(6))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));

        $group1 = new Group(42, $clientMock);
        $group2 = new Group(43, $clientMock);


        $this->factoryMock->expects($this->at(0))
                          ->method('createClient')
                          ->will($this->returnValue($clientMock));
        $this->factoryMock->expects($this->at(1))
                          ->method('loadGroup')
                          ->with(42, $clientMock)
                          ->will($this->returnValue($group1));
        $this->factoryMock->expects($this->at(2))
                          ->method('loadGroup')
                          ->with(43, $clientMock)
                          ->will($this->returnValue($group2));

        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['server-url', 'my.server'],
                                                      ['auth-token', 'my.token'],
                                                      ['source-group', 'mySourceGroup'],
                                                      ['destination-group', 'myDestGroup']]));

        $this->outputMock->expects($this->at(0))
                         ->method('writeln')
                         ->with('<info>add the member user1 to group myDestGroup with access level 10</info>');

        $this->outputMock->expects($this->at(1))
                         ->method('writeln')
                         ->with('<info>add the member user2 to group myDestGroup with access level 20</info>');

        $this->command->run($this->inputMock, $this->outputMock);
    }

    /**
     * @test
     */
    public function run_with_existent_members () {
        $clientMock = $this->getMockBuilder(Client::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $groupsMock = $this->getMockBuilder(Groups::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $groupsMock->expects($this->at(0))
                   ->method('search')
                   ->with('mySourceGroup')
                   ->will($this->returnValue([0 => ['id' => 42, 'name' => 'mySourceGroup']]));
        $groupsMock->expects($this->at(1))
                   ->method('search')
                   ->with('myDestGroup')
                   ->will($this->returnValue([0 => ['id' => 43, 'name' => 'myDestGroup']]));
        $groupsMock->expects($this->at(2))
                   ->method('members')
                   ->with(42)
                   ->will($this->returnValue([['id' => 1, 'username' => 'user1', 'access_level' => 10],
                                              ['id' => 2, 'username' => 'user2', 'access_level' => 20]]));
        $groupsMock->expects($this->at(3))
                   ->method('members')
                   ->with(43)
                   ->will($this->returnValue([['id' => 2, 'username' => 'user2', 'access_level' => 30]]));
        $groupsMock->expects($this->at(4))
                   ->method('addMember')
                   ->with(43, 1, 10)
                   ->will($this->returnValue([]));

        $clientMock->expects($this->at(0))
                   ->method('authenticate')
                   ->with('my.token', Client::AUTH_URL_TOKEN);
        $clientMock->expects($this->at(1))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(2))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(3))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(4))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));
        $clientMock->expects($this->at(5))
                   ->method('api')
                   ->with('groups')
                   ->will($this->returnValue($groupsMock));

        $group1 = new Group(42, $clientMock);
        $group2 = new Group(43, $clientMock);


        $this->factoryMock->expects($this->at(0))
                          ->method('createClient')
                          ->will($this->returnValue($clientMock));
        $this->factoryMock->expects($this->at(1))
                          ->method('loadGroup')
                          ->with(42, $clientMock)
                          ->will($this->returnValue($group1));
        $this->factoryMock->expects($this->at(2))
                          ->method('loadGroup')
                          ->with(43, $clientMock)
                          ->will($this->returnValue($group2));

        $this->inputMock->expects($this->any())
                        ->method('getOption')
                        ->will($this->returnValueMap([['server-url', 'my.server'],
                                                      ['auth-token', 'my.token'],
                                                      ['source-group', 'mySourceGroup'],
                                                      ['destination-group', 'myDestGroup']]));

        $this->outputMock->expects($this->at(0))
                         ->method('writeln')
                         ->with('<info>add the member user1 to group myDestGroup with access level 10</info>');

        $this->outputMock->expects($this->at(1))
                         ->method('writeln')
                         ->with('<info>the member user2 already added to the group myDestGroup</info>');

        $this->command->run($this->inputMock, $this->outputMock);
    }
}