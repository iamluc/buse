<?php

namespace Buse\Command;

use Gitonomy\Git\Commit;
use Gitonomy\Git\Reference\Branch;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('status')
            ->setDescription('Get status of your repositories')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groups = $this->getRepositories();

        $repositories = [];
        $status = [];
        foreach ($groups as $group => $repos) {
            foreach ($repos as $name => $repo) {
                $key = count($groups) > 1 ? $group.'/'.$name : $name;

                $state = $repo->getStatus();

                if (!$state['clean']) {
                    $tags = ['<fg=red>', '</fg=red>'];
                } elseif (!$state['sync']) {
                    $tags = ['<fg=magenta>', '</fg=magenta>'];
                } else {
                    $tags = ['<info>', '</info>'];
                }

                $head = $repo->getHead();
                if ($head instanceof Branch) {
                    $name = $head->getName();
                } elseif ($head instanceof Commit) {
                    $name = sprintf('detached (%s)', $head->getShortHash());
                } else {
                    $name = 'empty';
                }

                $status[$key] = sprintf('%1$s%3$s%2$s', $tags[0], $tags[1], $name);

                if (!$state['clean']) {
                    $status[$key] .= sprintf(' / not clean (%d staged, %d modified)', $state['staged'], $state['working']);
                }

                if (!$state['sync']) {
                    $status[$key] .= sprintf(' / not synchronized (%d ahead, %d behind)', $state['ahead'], $state['behind']);
                }

                $repositories[$key] = $repo;
            }
        }

        $this->display($repositories, $status, false);
    }
}
