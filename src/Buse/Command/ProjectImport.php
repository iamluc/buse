<?php

namespace Buse\Command;

use Buse\Console\Formatter\Spinner;
use Buse\Git\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ProjectImport extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('project:import')
            ->setDescription('Initialize project\'s repositories')
            ->addArgument(
                'config',
                InputArgument::REQUIRED,
                'Configuration file describing the project'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $config = Yaml::parse(file_get_contents($input->getArgument('config')));

        $repositories = [];
        foreach ($config['repositories'] as $dir => $url) {
            $repositories[] = $dir;
            $formatters[] = new Spinner(sprintf('Cloning %s...', $url), 'Done');
            $processes[] = $this->getProcess($dir, 'git', ['clone', $url, $dir]);
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }
}
