<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Buse\Console\Formatter\Spinner;

class Pull extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pull')
            ->setDescription('Pull your repositories')
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
            ->addOption(
                'ff-only',
                'f',
                InputOption::VALUE_NONE,
                'Fast forward only'
            )
            ->addOption(
                'rebase',
                'r',
                InputOption::VALUE_NONE,
                'Rebase instead of merge'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $repositories = $this->findRepositories();

        $args = ['pull'];
        if ($input->getOption('rebase')) {
            $args[] = '--rebase';
        } elseif ($input->getOption('ff-only')) {
            $args[] = '--ff-only';
        }

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
            $formatters[] = new Spinner(sprintf('pulling %s...', $ref));
            $processes[] = $this->getProcess($repo, 'git', $args);
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }
}
