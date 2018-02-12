<?php

declare(strict_types=1);

namespace Lit\Caravel\Commands;

use Lit\Caravel\Config;
use Lit\Caravel\Location\AbstractLocation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{
    /**
     * @var Config
     */
    protected $config;

    protected function configure()
    {
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'config file/path', '.');
    }
    
    protected function loadConfig(InputInterface $input)
    {
        if (!isset($this->config)) {
            $this->config = new Config();
            $this->config->load(
                $input->hasOption('config')
                    ? $input->getOption('config')
                    : '.'
            );
        }

        return $this->config;
    }

    protected function parseLocation(string $location): ?AbstractLocation
    {
        if (isset($this->config['location'][$location])) {
            return AbstractLocation::create($this->config['location'][$location]);
        }

        parse_str($location, $arr);

        return AbstractLocation::create($arr);
    }
}
