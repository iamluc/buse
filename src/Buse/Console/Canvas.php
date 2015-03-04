<?php

namespace Buse\Console;

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

    public function display(array $repositories, array $formatters)
    {
        if ($this->started) {
            $this->output->write(str_repeat("\033[1A", count($repositories)));
        } else {
            $this->started = true;
        }

        // Length of repository name column
        $length = 0;
        foreach ($repositories as $repo) {
            $length = max($length, strlen(basename($repo->getWorkingDir())));
        }

        // Print
        foreach ($repositories as $i => $repo) {
            $str = sprintf('<comment>%s</comment>: ', str_pad(basename($repo->getWorkingDir()), $length));

            $formatter = isset($formatters[$i]) ? $formatters[$i] : '';
            if ($formatter instanceof FormatterInterface) {
                $formatter = $formatter->display($this->cols - strlen($str));
            }

            $this->output->writeln(str_pad(substr($str.$formatter, 0, $this->cols), $this->cols));
        }
    }
}
