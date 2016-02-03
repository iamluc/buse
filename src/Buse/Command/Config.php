<?php

namespace Buse\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class Config extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('config')
            ->setDescription('Get and set configuration')
            ->addArgument(
                'option',
                InputArgument::OPTIONAL,
                'The name of the option'
            )
            ->addArgument(
                'value',
                InputArgument::OPTIONAL,
                'The value of the option'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleInput($input);

        $option = $input->getArgument('option');
        $value = $input->getArgument('value');

        if ($option && $value) {
            $this->get('config')->set($option, $value);
        } elseif ($option) {
            $output->writeln(Yaml::dump($this->get('config')->get($option), 10));
        } else {
            $output->writeln(Yaml::dump($this->get('config')->getConfig(), 10));
        }
    }
}
