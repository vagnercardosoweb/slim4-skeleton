<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/06/2020 Vagner Cardoso
 */

namespace Core;

use Core\Helpers\Env;
use Core\Helpers\Path;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * Class Bootstrap.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class App
{
    public const VERSION = '1.0.0';

    protected SlimApp $app;

    public function __construct()
    {
        Env::initialize();

        $this->configurePhpSettings();
        $this->configureApplication();
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->app->{$name}(...$arguments);
    }

    public static function isCli(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
    }

    public static function isApi(): bool
    {
        return true === Env::get('APP_ONLY_API', false);
    }

    public static function isTesting(): bool
    {
        return 'testing' === Env::get('APP_ENV', 'testing');
    }

    public function registerRoutes(): App
    {
        $path = Path::app('/routes');

        /** @var \DirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path, \FilesystemIterator::SKIP_DOTS
            )
        );

        $iterator->rewind();

        while ($iterator->valid()) {
            if ('php' === $iterator->getExtension()) {
                extract(['app' => $this->app]);

                require_once "{$iterator->getRealPath()}";
            }

            $iterator->next();
        }

        return $this;
    }

    protected function configurePhpSettings(): void
    {
        $locale = Env::get('APP_LOCALE', 'pt_BR');
        $charset = Env::get('APP_CHARSET', 'UTF-8');

        ini_set('default_charset', $charset);
        date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Sao_Paulo'));
        mb_internal_encoding($charset);
        setlocale(LC_ALL, $locale, "{$locale}.{$charset}");

        ini_set('display_errors', Env::get('PHP_DISPLAY_ERRORS', ini_get('display_errors')));
        ini_set('display_startup_errors', Env::get('PHP_DISPLAY_STARTUP_ERRORS', ini_get('display_startup_errors')));

        ini_set('log_errors', Env::get('PHP_LOG_ERRORS', true));
        ini_set('error_log', sprintf(Env::get('PHP_ERROR_LOG', Path::storage('/logs/php/%s.log')), date('dmY')));

        if ('development' === Env::get('APP_ENV', 'development')) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });
    }

    protected function configureApplication(): App
    {
        $containerBuilder = new ContainerBuilder();

        if (Env::get('APP_CONTAINER_COMPILE', false)) {
            $containerBuilder->enableCompilation(
                Path::storage('/cache/container')
            );
        }

        $containerBuilder->addDefinitions([
            App::class => function (ContainerInterface $container) {
                AppFactory::setContainer($container);

                return AppFactory::create();
            },

            ResponseFactoryInterface::class => function (ContainerInterface $container) {
                return $container->get(App::class)->getResponseFactory();
            },

            RouteParserInterface::class => function (ContainerInterface $container) {
                return $container->get(App::class)->getRouteCollector()->getRouteParser();
            },
        ]);

        $container = $containerBuilder->build();

        $this->app = $container->get(App::class);

        $this->app->addBodyParsingMiddleware();
        $this->app->addRoutingMiddleware();

        $displayErrorDetails = true;
        $logErrors = true;
        $logErrorDetails = true;

        $this->app->addErrorMiddleware(
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails
        );

        return $this;
    }
}
