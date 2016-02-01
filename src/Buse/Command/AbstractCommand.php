<?php

namespace Buse\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Buse\ContainerAwareInterface;
use Buse\Git\Repository;

class AbstractCommand extends Command implements ContainerAwareInterface
{
    protected $container;

    protected $path;
    protected $all;

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
        if ($input->hasArgument('path')) {
            $path = $input->getArgument('path');
            $this->path = realpath($path);
            $this->container['config_path'] = $this->path;

            if (!is_dir($this->path)) {
                throw new \Exception(sprintf('Invalid path "%s".', $path));
            }
        }

        $this->all = $input->getOption('all');

        return $this;
    }

    public function findRepositories()
    {
        $exclude = [];

        if (!$this->all) {
            $exclude = $this->get('config')->get('repositories.exclude');
            if (!is_array($exclude)) {
                $exclude = explode(',', $exclude);
            }
        }

        return $this->get('repository_manager')->findRepositories($this->path, $exclude);
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
