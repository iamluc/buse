<?php

namespace Buse\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\ProcessBuilder;
use Buse\ContainerAwareInterface;
use Buse\Git\Repository;

class AbstractCommand extends Command implements ContainerAwareInterface
{
    private $container;

    private $groups = [];
    private $noIgnore = false;

    protected function configure()
    {
        $this
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to the config file (".buse.yml")'
            )
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'The group(s) to use'
            )
            ->addOption(
                'working-dir',
                'w',
                InputOption::VALUE_REQUIRED,
                'If specified, use the given directory as working directory.'
            )
            ->addOption(
                'no-ignore',
                null,
                InputOption::VALUE_NONE,
                'Select all repositories, even those in "global.ignore_repositories"'
            )
        ;
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    public function get($id)
    {
        return $this->container[$id];
    }

    public function handleInput(InputInterface $input)
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
            foreach ($groups as $group) {
                if (false === $this->get('config')->hasGroup($group)) {
                    throw new \RuntimeException(sprintf('Group "%s" does not exist', $group));
                }
            }

            $this->groups = $groups;
        }

        $this->noIgnore = $input->getOption('no-ignore');

        return $this;
    }

    public function getRepositories()
    {
        if ($groups = $this->groups) {
            return $this->getGroupsRepositories($groups);
        }

        return $this->findRepositories();
    }

    public function findRepositories()
    {
        $exclude = [];
        if (!$this->noIgnore && $conf = $this->get('config')->get('global.ignore_repositories')) {
            if (is_array($conf)) {
                $exclude = $conf;
            } else {
                $exclude = explode(',', $conf);
            }
        }

        return $this->get('repository_manager')->findRepositories($this->get('working_dir'), $exclude);
    }

    public function getGroupsRepositories(array $groups)
    {
        $repositories = [];
        $workingDir = $this->get('working_dir');
        foreach ($groups as $group) {
            $prefix = $this->get('config')->get($group.'.prefix');

            $groupRepos = array_map(function ($name) use ($prefix, $workingDir) {
                return $workingDir.'/'.$prefix.$name;
            }, array_keys($this->get('config')->get($group.'.repositories')));

            $repositories = array_merge($repositories, $groupRepos);
        }

        return array_map(function ($name) {
            return new Repository($name);
        }, $repositories);
    }

    public function display(array $repositories, array $formatters)
    {
        return $this->get('canvas')->display($repositories, $formatters);
    }

    protected function runProcesses(array $repositories, array $processes, array $formatters)
    {
        while (count($processes) > 0) {
            foreach ($processes as $i => $process) {
                if (!$process->isStarted()) {
                    $process->start();

                    continue;
                }

                $out = $process->getIncrementalOutput();
                $error = $process->getIncrementalErrorOutput();

                $out = $out ?: $error;
                if ($out) {
                    $formatters[$i]->setMessage($out);
                }

                if (!$process->isRunning()) {
                    $formatters[$i]->finish($process->getExitCode());
                    unset($processes[$i]);
                }
            }

            $this->display($repositories, $formatters);

            if (count($processes)) {
                sleep(1);
            }
        }

        $this->get('canvas')->stop();
    }

    protected function getProcess($repo, $command, $args = array())
    {
        $base = [];
        if ($repo instanceof Repository) {
            $base = ['--git-dir', $repo->getGitDir()];
            if ($repo->getWorkingDir()) {
                $base = array_merge($base, ['--work-tree', $repo->getWorkingDir()]);
            }
        }

        $builder = new ProcessBuilder(array_merge([$command], $base, $args));
//        $builder->inheritEnvironmentVariables(false);
        $process = $builder->getProcess();
//        $process->setEnv($this->environmentVariables);
//        $process->setTimeout($this->processTimeout);
//        $process->setIdleTimeout($this->processTimeout);

        return $process;
    }
}
