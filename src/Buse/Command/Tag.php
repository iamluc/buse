<?php

namespace Buse\Command;

use Buse\Console\Formatter\Spinner;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Tag extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('tag')
            ->setDescription('Get and create tags')
            ->addArgument(
                'tagname',
                InputArgument::OPTIONAL,
                'Tag name'
            )
            ->addOption(
                'delete',
                'd',
                InputOption::VALUE_NONE,
                'Delete tag'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $repositories = $this->getRepositories();

        if ($tagName = $input->getArgument('tagname')) {
            $this->createTag($input, $repositories, $tagName);
        } else {
            $this->displayTags($repositories);
        }
    }

    protected function displayTags(array $repositories)
    {
        $status = [];
        foreach ($repositories as $i => $repo) {
            $tags = [];
            foreach ($repo->getReferences()->getTags() as $tag) {
                $tags[] = $tag->getName();
            }
            $status[$i] = implode(', ', $tags);
        }

        $this->display($repositories, $status);
    }

    protected function createTag(InputInterface $input, array $repositories, $tagName)
    {
        $args = ['tag'];
        $message = 'tagging tag %s...';
        if ($input->getOption('delete')) {
            $args[] = '--delete';
            $message = 'deleting tag %s...';
        }
        $args[] = $tagName;

        $formatters = [];
        $processes = [];
        foreach ($repositories as $repo) {
            $formatters[] = new Spinner(sprintf($message, $tagName));
            $processes[] = $this->getProcess($repo, 'git', $args);
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }
}
