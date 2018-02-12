<?php

declare(strict_types=1);

namespace Lit\Caravel\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ListPlanCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('list-plan');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Available plans:</info>');
        $this->loadConfig($input);
        foreach ($this->config['plan'] ?? [] as $key => $p) {
            $output->writeln('<comment>' . $key . '</comment>');
            $output->writeln(Yaml::dump($p, 4, 2));
        }
    }
}
