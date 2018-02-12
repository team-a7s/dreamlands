<?php

declare(strict_types=1);

namespace Lit\Caravel\Commands;

use Lit\Caravel\Location\AbstractLocation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('diff')
            ->setDescription('Compare two location and output difference')
            ->addArgument('from', InputArgument::REQUIRED)
            ->addArgument('to', InputArgument::REQUIRED)
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var AbstractLocation[] $dst */
        $dst = [];
        $this->loadConfig($input);
        foreach ($input->getOption('output') as $o) {
            $loc = $this->parseLocation($o);
            if (!$loc) {
                $output->writeln("<error>bad output specified: {$o}</error>");
                return -1;
            }

            $dst[] = $loc;
        }

        $from = $this->parseLocation($input->getArgument('from'));
        if (!$from) {
            $output->writeln('<error>illegal from specified!</error>');
            return -1;
        }
        $to = $this->parseLocation($input->getArgument('to'));
        if (!$to) {
            $output->writeln('<error>illegal to specified!</error>');
            return -1;
        }


        $diff = $from->diff($to->read());

        foreach ($dst as $location) {
            $location->update($diff);
        }

        return 0;
    }
}
