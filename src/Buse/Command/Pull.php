<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Pull extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('pull')
            ->setDescription('Pull your repositories')
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
        $args = [];
        if ($input->getOption('rebase')) {
            $args[] = '--rebase';
        } elseif ($input->getOption('ff-only')) {
            $args[] = '--ff-only';
        }

        if ($remote = $input->getArgument('remote')) {
            $args[] = $remote;

            if ($branch = $input->getArgument('branch')) {
                $args[] = $branch;
            }
        }

        $this->runGitCommand('pull', $args, 'Waiting to pull...');
    }
}
