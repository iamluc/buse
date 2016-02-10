<?php

namespace Buse\Command;

use Buse\Console\Formatter\Spinner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CloneCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('clone')
            ->setDescription('clone repositories defined in config file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$groups = $this->get('config')->getGroupsConfig($input->getOption('group'))) {
            throw new \RuntimeException('No repositories found to clone.');
        }

        $repositories = [];
        $formatters = [];
        $processes = [];
        foreach ($groups as $group => $config) {
            if (!isset($config['repositories'])) {
                continue;
            }
            $prefix = isset($config['prefix']) ? $config['prefix'] : null;

            foreach ($config['repositories'] as $name => $url) {
                $key = count($groups) > 1 ? $group.'/'.$name : $name;
                $dir = $prefix.$name;

                $repositories[$key] = $dir;
                if (null !== $url) {
                    $formatters[$key] = new Spinner(sprintf('Waiting to clone %s...', $url), 'Done');
                    $processes[$key] = $this->createGitProcess($dir, 'clone', [$url, $dir]);
                } else {
                    $formatters[$key] = 'Repository has no URL to clone';
                }
            }
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }
}
