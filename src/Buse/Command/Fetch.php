<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addOption(
                'prune',
                'p',
                InputOption::VALUE_NONE,
                'After fetching, remove any remote-tracking references that no longer exist on the remote'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = [];
        if ($remote = $input->getArgument('remote')) {
            $args[] = $remote;
        }

        if ($input->getOption('prune')) {
            $args[] = '--prune';
        }

        $this->runGitCommand('fetch', $args, 'Waiting to fetch...');
    }
}
