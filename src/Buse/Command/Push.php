<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Buse\Console\Formatter\Spinner;

class Push extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('push')
            ->setDescription('Push your repositories')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path',
                '.'
            )
            ->addArgument(
                'remote',
                InputArgument::OPTIONAL,
                'Remote'
            )
            ->addArgument(
                'branch',
                InputArgument::OPTIONAL,
                'Branch'
            )

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $repositories = $this->findRepositories();

        $args = ['push'];
        $ref = '';
        if ($remote = $input->getArgument('remote')) {
            $args[] = $remote;
            $ref = $remote;

            if ($branch = $input->getArgument('branch')) {
                $args[] = $branch;
                $ref .= '/'.$branch;
            }
        }

        $formatters = [];
        $processes = [];
        foreach ($repositories as $repo) {
            $formatters[] = new Spinner(sprintf('pushing %s...', $ref));
            $processes[] = $this->getProcess($repo, 'git', $args);
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }
}
