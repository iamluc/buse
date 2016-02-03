<?php

namespace Buse\Git;

use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\Repository as BaseRepository;

class Repository extends BaseRepository
{
    public function getStatus()
    {
        $output = $this->run('status', ['--porcelain']);
        $staged = count(preg_grep('/^(R|A|M|D)/', explode("\n", $output)));
        $working = count(preg_grep('/^.(R|A|M|D)/', explode("\n", $output)));

        try {
            $output = $this->run('log', ['--pretty=oneline', '--left-right', 'HEAD...'.'@{upstream}']);
            $ahead = count(preg_grep('/^< /', explode("\n", $output)));
            $behind = count(preg_grep('/^> /', explode("\n", $output)));
        } catch (ProcessException $e) {
            // assume there is no upstream
            $ahead = null;
            $behind = null;
        }

        return [
            'clean' => !$staged && !$working,
            'staged' => $staged,
            'working' => $working,
            'sync' => !$ahead && !$behind,
            'ahead' => $ahead,
            'behind' => $behind,
        ];
    }
}
