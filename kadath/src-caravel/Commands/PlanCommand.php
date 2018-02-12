<?php

declare(strict_types=1);

namespace Lit\Caravel\Commands;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlanCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('plan')
            ->addArgument('plan', InputArgument::REQUIRED);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input);
        $planName = $input->getArgument('plan');
        if (!isset($this->config['plan'][$planName])) {
            $output->writeln('<error>plan not found</error>');
            return -1;
        }
        $plan = $this->config['plan'][$planName];

        if (isset($plan['input']) && isset($plan['output'])) {
            $command = $this->getApplication()->find('convert');
            $realInput = new ArrayInput([
                'input' => $plan['input'],
                '-o' => (array)$plan['output'],
                '-c' => $input->getOption('config'),
            ]);
            return $command->run($realInput, $output);
        }

        if (
            isset($plan['from'])
            && isset($plan['to'])
            && isset($plan['output'])
        ) {
            $command = $this->getApplication()->find('diff');
            $realInput = new ArrayInput([
                'from' => $plan['from'],
                'to' => $plan['to'],
                '-o' => (array)$plan['output'],
                '-c' => $input->getOption('config'),
            ]);
            return $command->run($realInput, $output);
        }

        $output->writeln('<error>malformed plan</error>');
        return -1;
    }


}
