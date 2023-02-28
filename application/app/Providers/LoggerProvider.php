<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace App\Providers;

use Core\Interfaces\ServiceProvider;
use Core\Logger;
use Core\Support\Env;
use Core\Support\Path;
use DI\Container;
use Monolog\Level;
use Monolog\Processor\HostnameProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class LoggerProvider implements ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return LoggerInterface
     */
    public function __invoke(Container $container): LoggerInterface
    {
        $logger = new Logger(Env::get('LOGGER_NAME', 'app'));

        $logger->pushProcessor(new HostnameProcessor());
        $logger->pushProcessor(new ProcessIdProcessor());

        $path = 'development' === Env::get('APP_ENV') ? 'php://stdout'
            : sprintf(Path::storage('/logs/app/%s.log'), date('Y-m-d'));
        $logger->addStreamHandler($path);

        if (Env::get('SLACK_ENABLED', false)) {
            $logger->addSlackHandler(
                token: Env::get('SLACK_TOKEN'),
                channel: Env::get('SLACK_CHANNEL'),
                username: Env::get('SLACK_USERNAME'),
                icon: Env::get('SLACK_ICON', ''),
                level: Level::fromValue(Env::get('SLACK_LEVEL', Level::Error))
            );
        }

        return $logger;
    }
}
