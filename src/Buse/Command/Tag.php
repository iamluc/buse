<?php

namespace Buse\Command;

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
        if ($tagName = $input->getArgument('tagname')) {
            $this->createOrRemoveTag($input, $tagName);
        } else {
            $this->displayTags($output);
        }
    }

    protected function displayTags(OutputInterface $output)
    {
        $groups = $this->getRepositories();
        foreach ($groups as $group => $repos) {
            foreach ($repos as $name => $repo) {
                $key = count($groups) > 1 ? $group.'/'.$name : $name;

                $tags = [];
                foreach ($repo->getReferences()->getTags() as $tag) {
                    $tags[] = $tag->getName();
                }
                rsort($tags, SORT_NATURAL);

                $output->write(sprintf('<comment>%s</comment> (%d): ', $key, count($tags)));
                if ($tags) {
                    $output->writeln(implode(', ', $tags));
                } else {
                    $output->writeln('<error>No tag found</error>');
                }
            }
        }
    }

    protected function createOrRemoveTag(InputInterface $input, $tagName)
    {
        $args = [];
        $message = 'Waiting to tag...';
        if ($input->getOption('delete')) {
            $args[] = '--delete';
            $message = 'Waiting to delete tag...';
        }
        $args[] = $tagName;

        $this->runGitCommand('tag', $args, $message);
    }
}
