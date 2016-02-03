<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Buse\Console\Formatter\Spinner;

class Fetch extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('fetch')
            ->setDescription('Fetch your repositories')
            ->addArgument(
                'remote',
                InputArgument::OPTIONAL,
                'Remote'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $repositories = $this->getRepositories();

        $args = ['fetch'];
        if ($remote = $input->getArgument('remote')) {
            $args[] = $remote;
        }

        $formatters = [];
        $processes = [];
        foreach ($repositories as $repo) {
            $formatters[] = new Spinner(sprintf('fetching %s...', $remote), 'Done');
            $processes[] = $this->getProcess($repo, 'git', $args);
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }
}
