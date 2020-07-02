<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 02/07/2020 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core;

use Core\Facades\Facade;
use Core\Facades\Request;
use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownHandler;
use Core\Helpers\Env;
use Core\Helpers\Path;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App as SlimApplication;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Interfaces\RouteParserInterface;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\ResponseEmitter;

/**
 * Class Bootstrap.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class App
{
    public const VERSION = '1.0.0';

    protected SlimApplication $app;

    public function __construct()
    {
        Env::initialize();

        $this->configurePhpSettings();
        $this->configureSlimApplication();
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

    public function registerPathRoute(string $path): App
    {
        extract(['app' => $this->app]);

        require_once "{$path}";

        return $this;
    }

    public function registerFolderRoutes(?string $path = null): App
    {
        $path = $path ?? Path::app('/routes');

        /** @var \DirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path, \FilesystemIterator::SKIP_DOTS
            )
        );

        $iterator->rewind();

        while ($iterator->valid()) {
            if ('php' === $iterator->getExtension()) {
                $this->registerPathRoute($iterator->getRealPath());
            }

            $iterator->next();
        }

        return $this;
    }

    public function run(?ServerRequestInterface $request = null)
    {
        if (!$request) {
            $request = Request::getFacadeRoot();
        }

        $response = $this->app->handle($request);
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
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

        ini_set('log_errors', Env::get('PHP_LOG_ERRORS', 'true'));
        ini_set('error_log', sprintf(Env::get('PHP_ERROR_LOG', Path::storage('/logs/php/%s.log')), date('dmY')));
    }

    protected function configureSlimApplication(): App
    {
        $containerBuilder = new ContainerBuilder();

        if (Env::get('APP_CONTAINER_COMPILE', false)) {
            $containerBuilder->enableCompilation(
                Path::storage('/cache/container')
            );
        }

        $this->configureDefaultContainerBuilder($containerBuilder);
        $container = $containerBuilder->build();

        $this->app = $container->get(SlimApplication::class);

        Facade::setFacadeApplication($this->app);
        Facade::registerAliases();

        $routeCollector = $this->app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

        $this->app->addBodyParsingMiddleware();
        $this->app->addRoutingMiddleware();
        $this->app->add(new MethodOverrideMiddleware());

        $this->configureErrorHandler($container);

        return $this;
    }

    protected function configureErrorHandler(ContainerInterface $container): void
    {
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

        $request = $container->get(ServerRequestInterface::class);
        $logErrors = true;
        $logErrorDetails = true;
        $displayErrorDetails = true;

        $errorHandler = new HttpErrorHandler($this->app->getCallableResolver(), $this->app->getResponseFactory());
        $shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
        register_shutdown_function($shutdownHandler);

        $errorMiddleware = $this->app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);
    }

    protected function configureDefaultContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $providers = [];

        if (file_exists($pathProviders = Path::config('/providers.php'))) {
            $providers = require_once "{$pathProviders}";
        }

        $containerBuilder->addDefinitions($providers + [
            SlimApplication::class => function (ContainerInterface $container) {
                AppFactory::setContainer($container);

                return AppFactory::create();
            },

            ServerRequestInterface::class => function () {
                $serverRequestCreator = ServerRequestCreatorFactory::create();

                return $serverRequestCreator->createServerRequestFromGlobals();
            },

            ResponseFactoryInterface::class => function (ContainerInterface $container) {
                return $container->get(SlimApplication::class)->getResponseFactory();
            },

            RouteParserInterface::class => function (ContainerInterface $container) {
                return $container->get(SlimApplication::class)->getRouteCollector()->getRouteParser();
            },

            ResponseInterface::class => function (ContainerInterface $container) {
                return $container->get(ResponseFactoryInterface::class)->createResponse();
            },
        ]);
    }
}
