<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 23/01/2021 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core;

use Core\Facades\Facade;
use Core\Facades\ServerRequest;
use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownHandler;
use Core\Helpers\Env;
use Core\Helpers\Path;
use DI\Container;
use DI\ContainerBuilder;
use ErrorException;
use FilesystemIterator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Slim\App as SlimApplication;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\ResponseEmitter;
use function DI\factory;

/**
 * Class Bootstrap.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class App
{
    public const VERSION = '1.0.0';

    /**
     * @var \Slim\App
     */
    protected SlimApplication $app;

    /**
     * App constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        Env::initialize();

        $this->configurePhpSettings();
        $this->configureSlimApplication();
    }

    /**
     * @return bool
     */
    public static function isCli(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
    }

    /**
     * @return bool
     */
    public static function isApi(): bool
    {
        return true === Env::get('APP_ONLY_API', false);
    }

    /**
     * @return bool
     */
    public static function isTesting(): bool
    {
        return 'testing' === Env::get('APP_ENV', 'testing');
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function registerPathRoute(string $path): App
    {
        extract(['app' => $this->app]);

        require_once "{$path}";

        return $this;
    }

    /**
     * @param string|null $path
     *
     * @return $this
     */
    public function registerFolderRoutes(?string $path = null): App
    {
        $path = $path ?? Path::app('/routes');

        /** @var \DirectoryIterator $iterator */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path, FilesystemIterator::SKIP_DOTS
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

    /**
     * @param \Psr\Http\Message\ServerRequestInterface|null $request
     */
    public function run(?ServerRequestInterface $request = null)
    {
        $response = $this->app->handle($request ?? ServerRequest::getFacadeRoot());
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }

    /**
     * @param array|callable|null $middleware
     *
     * @return void
     */
    public function registerMiddleware($middleware = null): void
    {
        if (!$middleware && file_exists($path = Path::config('/middleware.php'))) {
            $middleware = require_once "{$path}";
        }

        if (is_array($middleware)) {
            foreach ($middleware as $class) {
                $this->app->add($class);
            }
        }

        if (is_callable($middleware)) {
            call_user_func($middleware, $this->app);
        }
    }

    /**
     * @return void
     */
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

    /**
     * @throws \Exception
     *
     * @return $this
     */
    protected function configureSlimApplication(): App
    {
        $container = $this->configureContainerBuilder();
        $this->app = $container->get(SlimApplication::class);

        Facade::setFacadeApplication($this->app);
        Facade::registerAliases();

        $routeCollector = $this->app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());

        $this->configureErrorHandler();

        return $this;
    }

    /**
     * @throws \ErrorException
     */
    protected function configureErrorHandler(): void
    {
        if ('development' === Env::get('APP_ENV', 'development')) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ALL ^ E_DEPRECATED);
        }

        set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });

        $serverRequest = ServerRequest::getFacadeRoot();
        $logErrors = Env::get('SLIM_LOG_ERRORS', true);
        $logErrorDetails = Env::get('SLIM_LOG_ERROR_DETAIL', true);
        $displayErrorDetails = Env::get('SLIM_DISPLAY_ERROR_DETAILS', true);

        $errorHandler = new HttpErrorHandler($this->app->getCallableResolver(), $this->app->getResponseFactory());
        $shutdownHandler = new ShutdownHandler($serverRequest, $errorHandler, $displayErrorDetails);
        register_shutdown_function($shutdownHandler);

        $errorMiddleware = $this->app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);
    }

    /**
     * @throws \Exception
     *
     * @return \DI\Container
     */
    protected function configureContainerBuilder(): Container
    {
        $providers = [];
        $containerBuilder = new ContainerBuilder();

        if (Env::get('CONTAINER_CACHE', false)) {
            $containerBuilder->enableCompilation(
                Path::storage('/cache/container')
            );
        }

        $containerBuilder->useAutowiring(Env::get('CONTAINER_AUTO_WIRING', false));

        if (file_exists($path = Path::config('/providers.php'))) {
            $providers = require_once "{$path}";
        }

        foreach ($providers as $key => $provider) {
            if (is_string($provider) && class_exists($provider)) {
                $providers[$key] = factory($provider);
            }
        }

        $containerBuilder->addDefinitions(array_merge($providers, [
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

            StreamFactoryInterface::class => function () {
                return new StreamFactory();
            },
        ]));

        return $containerBuilder->build();
    }
}
