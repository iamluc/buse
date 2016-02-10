<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Push extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('push')
            ->setDescription('Push your repositories')
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
        $args = [];
        if ($remote = $input->getArgument('remote')) {
            $args[] = $remote;

            if ($branch = $input->getArgument('branch')) {
                $args[] = $branch;
            }
        }

        $this->runGitCommand('push', $args, 'Waiting to push...');
    }
}
