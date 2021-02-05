<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/02/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Bootstrap;
use Core\Config;
use Core\Contracts\ServiceProvider;
use Core\Facades\Facade;
use DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class SymfonyConsoleProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class SymfonyConsoleProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return \Symfony\Component\Console\Application
     */
    public function __invoke(Container $container): Application
    {
        Facade::setAliases(['Application' => Application::class]);

        $application = new Application('Slim 4 Skeleton', Bootstrap::VERSION);

        $optionEnv = new InputOption(
            name: '--env',
            shortcut: '-e',
            mode: InputOption::VALUE_REQUIRED,
            description: 'The environment name.',
            default: 'development'
        );

        $application->getDefinition()->addOption($optionEnv);

        $commands = Config::get('commands');

        foreach ($commands as $command) {
            try {
                $application->add($container->get($command));
            } catch (\Exception $e) {
                $output = new ConsoleOutput();
                $output->writeln("Add command error: <error>{$command}</error>");
                $output->writeln($e->getMessage());
                // exit(0);
            }
        }

        return $application;
    }
}
