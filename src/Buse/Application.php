<?php

namespace Buse;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pimple\Container;
use Buse\Console\Canvas;
use Buse\Git\RepositoryManager;

class Application extends BaseApplication
{
    const VERSION = '0.0.1';

    public function __construct()
    {
        parent::__construct('Buse', self::VERSION);

        $this->getDefinition()->addOption(
            new InputOption('all')
        );
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $container = $this->createContainer($input, $output);

        foreach ($this->all() as $command) {
            if ($command instanceof ContainerAwareInterface) {
                $command->setContainer($container);
            }
        }

        return parent::doRun($input, $output);
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

        return $commands;
    }

    protected function createContainer(InputInterface $input, OutputInterface $output)
    {
        $container = new Container();

        $container['repository_manager'] = function ($c) {
            return new RepositoryManager();
        };

        $container['canvas'] = function ($c) use ($output) {
            return new Canvas($output, $this->getTerminalWidth());
        };

        $container['config_path'] = getcwd();
        $container['config_filename'] = '.buse';

        $container['config'] = function ($c) {
            return new Config($c['config_path'], $c['config_filename']);
        };

        return $container;
    }
}
