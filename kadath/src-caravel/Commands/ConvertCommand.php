<?php

declare(strict_types=1);

namespace Lit\Caravel\Commands;

use Lit\Caravel\Location\AbstractLocation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('convert')
            ->setDescription('Run conversion process')
            ->addArgument('input', InputArgument::REQUIRED)
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

        $src = $this->parseLocation($input->getArgument('input'));
        if (!$src) {
            $output->writeln('<error>illegal input specified!</error>');
            return -1;
        }

        $schema = $src->read();

        foreach ($dst as $location) {
            $location->write($schema);
        }

        return 0;
    }
}
