<?php
/**
 * Created by PhpStorm.
 * User: aappen
 * Date: 09.01.17
 * Time: 10:35
 */

namespace Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CoverageCheckCommand extends BaseCommand {
    protected function configure () {
        $this->setName('coverage:check')
             ->addOption('clover-file', 'c', InputOption::VALUE_REQUIRED, 'the path to the generated clover xml file')
             ->addOption('percentage', 'p', InputOption::VALUE_REQUIRED, 'the min coverage percentage')
             ->setDescription('the command returns 1 if the coverage below percentage.');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {
        /* @var $factory \ApplicationFactory */
        $factory = $this->getContainer()
                        ->get('factory');

        $fileName = $input->getOption('clover-file');

        $xml = $factory->createXmlElement($fileName);
        $metrics = $xml->xpath('//metrics');
        $totalElements = 0;
        $checkedElements = 0;

        foreach ($metrics as $metric) {
            $totalElements += (int) $metric['elements'];
            $checkedElements += (int) $metric['coveredelements'];
        }

        $coverage = ($checkedElements / $totalElements) * 100;

        if ($coverage < $input->getOption('percentage')) {
            $output->writeln('<error>Code coverage is '.$coverage.'%, which is below the accepted '.
                             $input->getOption('percentage').'%</error>');
            exit(1);
        }

        $output->writeln('<info>Code coverage is '.$coverage.'% - OK</info>');
    }
}