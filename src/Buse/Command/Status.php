<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Get status of your repositories')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path',
                '.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $repositories = $this->findRepositories();

        $length = 0;
        foreach ($repositories as $repo) {
            $length = max($length, strlen(basename($repo->getWorkingDir())));
        }

        $status = [];
        foreach ($repositories as $i => $repo) {
            $state = $repo->getStatus();

            if (!$state['clean']) {
                $tags = ['<fg=red>', '</fg=red>'];
            } elseif (!$state['sync']) {
                $tags = ['<fg=magenta>', '</fg=magenta>'];
            } else {
                $tags = ['<info>', '</info>'];
            }

            $status[$i] = sprintf('%1$s%3$s%2$s', $tags[0], $tags[1], $repo->getHead()->getName());

            if (!$state['clean']) {
                $status[$i] .= sprintf(' / not clean (%d staged, %d modified)', $state['staged'], $state['working']);
            }

            if (!$state['sync']) {
                $status[$i] .= sprintf(' / not synchronized (%d ahead, %d behind)', $state['ahead'], $state['behind']);
            }
        }

        $this->display($repositories, $status);
    }
}
