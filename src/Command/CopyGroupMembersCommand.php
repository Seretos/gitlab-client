<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 09.01.17
 * Time: 11:49
 */

namespace Command;


use Gitlab\Client;
use Gitlab\Model\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CopyGroupMembersCommand extends BaseCommand {
    protected function configure () {
        $this->setName('copy:members')
             ->addOption('server-url',
                         's',
                         InputOption::VALUE_REQUIRED,
                         'the gitlab server-url. for example: http://your.domain/api/v3/')
             ->addOption('auth-token', 't', InputOption::VALUE_REQUIRED, 'your gitlab user token')
             ->addOption('source-group', 'sg', InputOption::VALUE_REQUIRED, 'the source group with members')
             ->addOption('destination-group', 'dg', InputOption::VALUE_REQUIRED, 'the destination group to add members')
             ->setDescription('copy members from source group to destination group');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {
        /* @var $factory \ApplicationFactory */
        $factory = $this->getContainer()
                        ->get('factory');

        $client = $factory->createClient($input->getOption('server-url'));
        $client->authenticate($input->getOption('auth-token'), Client::AUTH_URL_TOKEN);

        $sourceJson = $client->api('groups')
                             ->search($input->getOption('source-group'));
        $destJson = $client->api('groups')
                           ->search($input->getOption('destination-group'));

        $sourceGroup = $factory->loadGroup($sourceJson[0]['id'], $client);
        $destGroup = $factory->loadGroup($destJson[0]['id'], $client);

        $sourceMembers = $sourceGroup->members();
        $destinationMembers = $destGroup->members();

        if (count($sourceMembers) == 0) {
            $output->writeln('<info>no members in source group!</info>');

            return 0;
        }

        foreach ($sourceMembers as $member) {
            if ($this->findMember($destinationMembers, $member)) {
                $output->writeln('<info>the member '.$member->username.' already added to the group '.
                                 $destJson[0]['name'].
                                 '</info>');
            } else {
                $output->writeln('<info>add the member '.$member->username.' to group '.$destJson[0]['name'].
                                 ' with access level '.$member->access_level.'</info>');
                $destGroup->addMember($member->id, $member->access_level);
            }
        }
    }

    /**
     * @param User[] $members
     * @param User   $currentMember
     *
     * @return bool
     */
    private function findMember (array $members, User $currentMember) {
        foreach ($members as $member) {
            if ($member->id == $currentMember->id) {
                return true;
            }
        }

        return false;
    }
}