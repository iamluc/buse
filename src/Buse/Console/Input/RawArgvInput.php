<?php

namespace Buse\Console\Input;

use Symfony\Component\Console\Input\ArgvInput;

class RawArgvInput extends ArgvInput
{
    protected $rawArgs;

    public function __construct(array $argv = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }

        $argv = $this->extractRawArgs($argv);

        parent::__construct($argv);
    }

    public function getRawArgs()
    {
        return $this->rawArgs;
    }

    protected function extractRawArgs(array $argv)
    {
        if (false === $offset = array_search('--', $argv, true)) {
            return;
        }

        $this->rawArgs = array_slice($argv, $offset + 1);

        return array_slice($argv, 0, $offset);
    }
}
