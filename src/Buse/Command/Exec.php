<?php

namespace Buse\Command;

use Buse\Console\Formatter\Spinner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Exec extends AbstractCommand
{
    protected $synopsis;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('exec')
            ->setDescription('Execute a command in your repositories')
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

        $args = $input->getRawArgs();
        if (!is_array($args) || 0 === count($args)) {
            throw new \Exception('You must write the command after --. i.e. "buse exec -- log -1"');
        }

        $cmd = array_shift($args);

        $repositories = [];
        $formatters = [];
        $processes = [];
        foreach ($groups as $group => $repos) {
            foreach ($repos as $name => $repo) {
                $key = count($groups) > 1 ? $group.'/'.$name : $name;

                $formatters[$key] = new Spinner(sprintf('Waiting to execute "%s"...', $cmd), 'Done');
                $processes[$key] = $this->createProcess($repo, $cmd, $args);
                $repositories[$key] = $repo;
            }
        }

        $this->runProcesses($repositories, $processes, $formatters);
    }
}
