<?php

namespace Buse\Command;

use Gitonomy\Git\Exception\ProcessException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Exec extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('exec')
            ->setDescription('Execute a git command in your repositories (do not specify `git` in your command)')
            ->addArgument(
                'cmd',
                InputArgument::REQUIRED,
                'Command'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path',
                '.'
            )
            ->addOption(
                'continue',
                'c',
                InputOption::VALUE_NONE,
                'Continue on error'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $repositories = $this->findRepositories();

        $continue = $input->getOption('continue');

        preg_match('/([^ ]*) *(.*)/', $input->getArgument('cmd'), $matches);
        list($full, $cmd, $args) = $matches;
        $args = preg_split('/ +/', $args, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($repositories as $repo) {
            $output->writeln(sprintf('<comment>%s</comment>: ', basename($repo->getWorkingDir())));

            try {
                $res = $repo->run($cmd, $args);
                $output->writeln($res);
            } catch (ProcessException $e) {
                if (!$continue) {
                    throw $e;
                }

                $output->writeln($e->getErrorOutput());
            }
        }
    }
}
