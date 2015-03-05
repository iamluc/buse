<?php

namespace Buse\Git;

use Symfony\Component\Finder\Finder;

class RepositoryManager
{
    public function findRepositories($path, array $exclude = [], $depth = '<2')
    {
        $path = realpath($path);

        $finder = new Finder();
        $finder->directories()
            ->name('.git')
            ->depth($depth)
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->in($path)
            ->sort(function (\SplFileInfo $file1, \SplFileInfo $file2) {
                return strnatcasecmp(basename($file1->getPath()), basename($file2->getPath()));
            })
        ;

        $repositories = [];
        foreach ($finder as $gitDir) {
            $workingDir = dirname($gitDir);

            if (in_array(basename($workingDir), $exclude)) {
                continue;
            }

            $repositories[] = new Repository($workingDir);
        }

        return $repositories;
    }
}
