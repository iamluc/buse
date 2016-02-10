<?php

namespace Buse\Console;

use Gitonomy\Git\Repository;
use Symfony\Component\Console\Output\OutputInterface;
use Buse\Console\Formatter\FormatterInterface;

class Canvas
{
    protected $output;
    protected $cols;

    protected $started;

    public function __construct(OutputInterface $output, $cols = 80)
    {
        $this->output = $output;
        $this->cols = $cols;
        $this->started = false;
    }

    public function stop()
    {
        $this->started = false;
    }

    public function writeln($messages)
    {
        $this->output->writeln($messages);
    }

    public function display(array $repositories, array $formatters, $dynamic = true)
    {
        if ($dynamic) {
            if ($this->started) {
                $this->output->write(str_repeat("\033[1A", count($repositories)));
            } else {
                $this->started = true;
            }
        }

        // Length of repository name column
        $length = 0;
        foreach ($repositories as $i => $repo) {
            $name = is_numeric($i) ? $this->getRepositoryName($repo) : $i;
            $length = max($length, strlen($name));
        }

        // Print
        foreach ($repositories as $i => $repo) {
            $name = is_numeric($i) ? $this->getRepositoryName($repo) : $i;
            $str = sprintf('<comment>%s</comment>: ', str_pad($name, $length));

            $formatter = isset($formatters[$i]) ? $formatters[$i] : '';
            if ($formatter instanceof FormatterInterface) {
                $formatter = $formatter->display($this->cols - strlen($str));
            }

            $this->output->writeln(str_pad(substr($str.$formatter, 0, $this->cols), $this->cols));
        }
    }

    protected function getRepositoryName($repo)
    {
        if ($repo instanceof Repository) {
            return basename($repo->getWorkingDir());
        }

        return $repo;
    }
}
