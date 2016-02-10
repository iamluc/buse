<?php

namespace Buse\Command;

use Gitonomy\Git\Exception\ProcessException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Git extends AbstractCommand
{
    protected $synopsis;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('git')
            ->setDescription('Execute a git command in your repositories (do not specify `git` in your command)')
            ->addOption(
                'continue',
                'e',
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
        $groups = $this->getRepositories();

        $continue = $input->getOption('continue');

        $args = $input->getRawArgs();
        if (!is_array($args) || 0 === count($args)) {
            throw new \Exception('You must write the command after --. i.e. "buse git -- log -1"');
        }

        $cmd = array_shift($args);

        foreach ($groups as $group => $repos) {
            foreach ($repos as $name => $repo) {
                $name = count($groups) > 1 ? $group.'/'.$name : $name;
                $output->writeln(sprintf('<comment>%s</comment>: ', $name));

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
}
