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
        $this->handleInput($input);

        $config = $this->get('config')->getGroupsConfig($input->getOption('group'));

        if (!$config) {
            throw new \RuntimeException('No repositories found to clone.');
        }

        foreach ($config as $group => $config) {
            $this->cloneGroup($group, $config['repositories'], isset($config['prefix']) ? $config['prefix'] : null);
        }
    }

    protected function cloneGroup($group, $repositoriesConfig, $prefix)
    {
        $repositories = [];
        $formatters = [];
        $processes = [];
        foreach ($repositoriesConfig as $dir => $url) {
            $dir = $prefix.$dir;

            $repositories[] = $dir;
            $formatters[] = new Spinner(sprintf('Waiting to clone %s...', $url), 'Done');
            $processes[] = $this->getProcess($dir, 'git', ['clone', $url, $dir]);
        }

        if ($repositories) {
            $this->get('canvas')->writeln('<info>'.$group.'</info>');
            $this->runProcesses($repositories, $processes, $formatters);
        }
    }
}
