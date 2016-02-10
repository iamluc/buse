<?php

namespace Buse;

use Buse\Process\ProcessManager;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as SfCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pimple\Container;
use Buse\Console\Canvas;
use Buse\Git\RepositoryManager;

class Application extends BaseApplication
{
    const VERSION = '0.0.3-dev';

    public function __construct()
    {
        parent::__construct('Buse', self::VERSION);
    }

    protected function doRunCommand(SfCommand $command, InputInterface $input, OutputInterface $output)
    {
        $container = $this->createContainer($input, $output);

        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($container);
        }

        return parent::doRunCommand($command, $input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(new InputOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Path to the config file (".buse.yml")'
        ));

        $definition->addOption(new InputOption(
            'working-dir',
            'w',
            InputOption::VALUE_REQUIRED,
            'If specified, use the given directory as working directory.'
        ));

        $definition->addOption(new InputOption(
            'group',
            'g',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'The group(s) to use'
        ));

        $definition->addOption(new InputOption(
            'no-ignore',
            null,
            InputOption::VALUE_NONE,
            'Select all repositories, even those in "global.ignore_repositories"'
        ));

        $definition->addOption(new InputOption(
            'no-group',
            null,
            InputOption::VALUE_NONE,
            'Search repositories instead of using groups defined in the config file'
        ));

        return $definition;
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\Config();
        $commands[] = new Command\Exec();
        $commands[] = new Command\Status();
        $commands[] = new Command\Fetch();
        $commands[] = new Command\Pull();
        $commands[] = new Command\Push();
        $commands[] = new Command\Tag();
        $commands[] = new Command\CloneCommand();
        $commands[] = new Command\Git();

        return $commands;
    }

    protected function createContainer(InputInterface $input, OutputInterface $output)
    {
        $container = new Container([
            'config_path' => getcwd(),
            'config_filename' => '.buse.yml',
            'working_dir' => getcwd(),
            'groups' => [],
            'no_ignore' => false,
        ]);

        $container['canvas'] = function ($c) use ($output) {
            return new Canvas($output, $this->getTerminalWidth());
        };

        $container['repository_manager'] = function ($c) {
            return new RepositoryManager();
        };

        $container['process_manager'] = function ($c) {
            return new ProcessManager($c['canvas']);
        };

        $container['config'] = function ($c) {
            return new Config($c['config_path'], $c['config_filename']);
        };

        return $container;
    }
}
