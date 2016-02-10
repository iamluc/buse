<?php

namespace Buse\Command;

use Buse\Console\Formatter\Spinner;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Buse\ContainerAwareInterface;
use Buse\Git\Repository;

class AbstractCommand extends Command implements ContainerAwareInterface
{
    private $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    protected function get($id)
    {
        return $this->container[$id];
    }

    protected function getRepositories()
    {
        if ($groups = $this->get('groups')) {
            return $this->getGroupsRepositories($groups);
        }

        return $this->findRepositories();
    }

    protected function findRepositories()
    {
        $exclude = [];
        if (!$this->get('no_ignore') && $conf = $this->get('config')->get('global.ignore_repositories')) {
            if (is_array($conf)) {
                $exclude = $conf;
            } else {
                $exclude = explode(',', $conf);
            }
        }

        if (!$repositories = $this->get('repository_manager')->findRepositories($this->get('working_dir'), $exclude)) {
            throw new \LogicException(sprintf('No repositories found in directory "%s"', $this->get('working_dir')));
        }

        return ['default' => $repositories];
    }

    protected function getGroupsRepositories(array $groups)
    {
        $repositories = [];
        $workingDir = $this->get('working_dir');

        $found = false;
        foreach ($groups as $group) {
            $config = $this->get('config')->get($group);
            $prefix = isset($config['prefix']) ? $config['prefix'] : '';

            if (!isset($config['repositories'])) {
                continue;
            }

            foreach ($config['repositories'] as $name => $url) {
                $found = true;
                $repositories[$group][$name] = new Repository($workingDir.'/'.$prefix.$name);
            }
        }

        if (!$found) {
            throw new \LogicException(sprintf('No repositories found in group(s) "%s"', implode(', ', $groups)));
        }

        return $repositories;
    }

    protected function runGitCommand($command, $args, $message = 'Waiting to run command...')
    {
        $groups = $this->getRepositories();

        $repositories = [];
        $formatters = [];
        $processes = [];
        foreach ($groups as $group => $repos) {
            foreach ($repos as $name => $repo) {
                $key = count($groups) > 1 ? $group.'/'.$name : $name;

                $repositories[$key] = $repo;
                $formatters[$key] = new Spinner($message, 'Done');
                $processes[$key] = $this->createGitProcess($repo, $command, $args);
            }
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }

    protected function display(array $repositories, array $formatters, $dynamic = true)
    {
        return $this->get('canvas')->display($repositories, $formatters, $dynamic);
    }

    protected function createProcess($repo, $command, $args = array())
    {
        return $this->get('process_manager')->createProcess($repo, $command, $args);
    }

    protected function createGitProcess($repo, $command, $args = array())
    {
        return $this->get('process_manager')->createGitProcess($repo, $command, $args);
    }

    protected function runProcesses(array $repositories, array $processes, array $formatters)
    {
        $this->get('process_manager')->runProcesses($repositories, $processes, $formatters);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('working-dir') && $workingDir = $input->getOption('working-dir')) {
            if (!is_dir($workingDir)) {
                throw new \Exception(sprintf('Invalid working directory "%s".', $workingDir));
            }

            $this->container['working_dir'] = realpath($workingDir);
        }

        if ($input->hasOption('config') && $config = $input->getOption('config')) {
            if (!is_dir($config)) {
                throw new \Exception(sprintf('Invalid config path "%s".', $config));
            }

            $this->container['config_path'] = realpath($config);
        } else {
            $this->container['config_path'] = $this->container['working_dir'];
        }

        if ($input->hasOption('group') && $groups = $input->getOption('group')) {
            if ($input->getOption('no-group')) {
                throw new \LogicException("Options --group and --no-group are mutually exlusive.");
            }

            $notFound = [];
            foreach ($groups as $group) {
                if (false === $this->get('config')->hasGroup($group)) {
                    $notFound[] = $group;
                }
            }

            if ($notFound) {
                throw new \RuntimeException(sprintf('Unable to find group(s) "%s"', implode(', ', $notFound)));
            }

            $this->container['groups'] = $groups;
        }

        if (!$input->getOption('no-group') && !$this->container['groups'] && $groups = $this->get('config')->getGroups()) {
            $this->container['groups'] = $groups;
        }

        $this->container['no_ignore'] = $input->getOption('no-ignore');
    }
}
