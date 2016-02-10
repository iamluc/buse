<?php

namespace Buse\Process;

use Buse\Console\Canvas;
use Buse\Git\Repository;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class ProcessManager
{
    const MAX_PROCESSES = 10;

    /**
     * @var Canvas
     */
    private $canvas;

    public function __construct(Canvas $canvas)
    {
        $this->canvas = $canvas;
    }

    public function createProcess($repo, $command, $args = array())
    {
        $builder = new ProcessBuilder(array_merge([$command], $args));
        $process = $builder->getProcess();

        if ($repo instanceof Repository) {
            if ($repo->getWorkingDir()) {
                $process->setWorkingDirectory($repo->getWorkingDir());
            }
        }

        return $process;
    }

    public function createGitProcess($repo, $command, $args = array())
    {
        $base = [];
        if ($repo instanceof Repository) {
            $base = ['--git-dir', $repo->getGitDir()];
            if ($repo->getWorkingDir()) {
                $base = array_merge($base, ['--work-tree', $repo->getWorkingDir()]);
            }
        }

        $builder = new ProcessBuilder(array_merge(['git'], $base, [$command], $args));

        return $builder->getProcess();
    }

    public function runProcesses(array $repositories, array $processes, array $formatters, $group = 'default')
    {
        // Force first display
        $this->canvas->display($repositories, $formatters, $group);

        $running = 0;
        while (count($processes) > 0) {
            /**
             * @var mixed
             * @var Process $process
             */
            foreach ($processes as $i => $process) {
                if (!$process->isStarted()) {
                    if ($running < self::MAX_PROCESSES) {
                        ++$running;
                        $process->start();
                    }

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
                    --$running;
                    unset($processes[$i]);
                }
            }

            $this->canvas->display($repositories, $formatters, $group);

            if (count($processes)) {
                sleep(1);
            }
        }

        $this->canvas->stop();
    }
}
