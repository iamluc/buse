<?php

namespace Buse\Command;

use Gitonomy\Git\Exception\ProcessException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Exec extends AbstractCommand
{
    protected $synopsis;

    protected function configure()
    {
        $this
            ->setName('exec')
            ->setDescription('Execute a git command in your repositories (do not specify `git` in your command)')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path',
                '.'
            )
            ->addOption(
                'continue',
                'c',
                InputOption::VALUE_NONE,
                'Continue on error'
            )
        ;
    }

    public function getSynopsis()
    {
        if (null === $this->synopsis) {
            $this->synopsis = trim(sprintf('%s %s -- command', $this->getName(), $this->getDefinition()->getSynopsis()));
        }

        return $this->synopsis;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $repositories = $this->findRepositories();

        $continue = $input->getOption('continue');

        $args = $input->getRawArgs();
        if (!is_array($args) || 0 === count($args)) {
            throw new \Exception('You must write the command after --. i.e. "buse exec -- log -1"');
        }

        $cmd = array_shift($args);

        foreach ($repositories as $repo) {
            $output->writeln(sprintf('<comment>%s</comment>: ', basename($repo->getWorkingDir())));

            try {
                $res = $repo->run($cmd, $args);
                $output->writeln($res);
            } catch (ProcessException $e) {
                if (!$continue) {
                    throw $e;
                }

                $output->writeln($e->getErrorOutput());
            }
        }
    }
}
