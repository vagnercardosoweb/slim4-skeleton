<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core;

use Core\Facades\Facade;
use Core\Facades\Logger as LoggerFacade;
use Core\Facades\ServerRequest;
use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownErrorHandler;
use Core\Support\Env;
use Core\Support\Path;
use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Slim\Psr7\Factory\UriFactory;

class Application
{
    public const VERSION = '1.0.0';

    private static ?App $app = null;

    public function __construct(
        protected ?string $pathRoutes = null,
        protected ?string $pathMiddleware = null,
        protected ?string $pathProviders = null,
        protected ?string $pathModules = null,
        protected ?bool $immutableEnv = false
    ) {
        Env::load($this->immutableEnv);

        $this->registerApp();
        $this->registerFacade();
        $this->registerPhpSettings();

        if ($this->runningWebserverOrTest()) {
            $this->registerMiddleware();
            $this->registerErrorHandler();

            Route::setRouteCollectorProxy(self::$app);

            if ($this->pathRoutes) {
                Route::registerPath($this->pathRoutes);
            }

            $this->registerModules();
        }
    }

    private function registerApp(): App
    {
        if (is_null(self::$app)) {
            $container = $this->registerContainerBuilder();
            self::$app = $container->get(App::class);
        }

        return self::$app;
    }

    private function registerContainerBuilder(): Container
    {
        $container = [];
        $containerPath = (string)$this->pathProviders;
        $containerBuilder = new ContainerBuilder();

        if (Env::get('CONTAINER_CACHE', false)) {
            $containerBuilder->enableCompilation(Path::storage('/cache/container'));
        }

        $containerBuilder->useAutowiring(Env::get('CONTAINER_AUTO_WIRING', true));

        if (file_exists($containerPath)) {
            $container = require_once "{$containerPath}";

            if (!is_array($container)) {
                throw new \DomainException(
                    "The [{$containerPath}] file must return an array."
                );
            }
        }

        $containerBuilder->addDefinitions(array_merge([
            App::class => function (ContainerInterface $container) {
                AppFactory::setContainer($container);

                return AppFactory::create();
            },

            UploadedFileFactoryInterface::class => fn () => new UploadedFileFactory(),
            UriFactoryInterface::class => fn () => new UriFactory(),

            ServerRequestInterface::class => function () {
                $serverRequestCreator = ServerRequestCreatorFactory::create();

                return $serverRequestCreator->createServerRequestFromGlobals();
            },

            ResponseFactoryInterface::class => function (ContainerInterface $container) {
                return $container->get(App::class)->getResponseFactory();
            },

            RouteCollectorInterface::class => function (ContainerInterface $container) {
                return $container->get(App::class)->getRouteCollector();
            },

            RouteParserInterface::class => function (ContainerInterface $container) {
                return $container->get(RouteCollectorInterface::class)->getRouteParser();
            },

            ResponseInterface::class => function (ContainerInterface $container) {
                return $container->get(ResponseFactoryInterface::class)->createResponse();
            },

            StreamFactoryInterface::class => function () {
                return new StreamFactory();
            },
        ], $container));

        return $containerBuilder->build();
    }

    private function registerFacade(): void
    {
        Facade::setApp(self::$app);
        Facade::registerAliases();
    }

    private function registerPhpSettings(): void
    {
        error_reporting(-1);

        $locale = Env::get('APP_LOCALE', 'pt_BR');
        $charset = Env::get('APP_CHARSET', 'UTF-8');

        ini_set('default_charset', $charset);
        date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Sao_Paulo'));
        mb_internal_encoding($charset);
        setlocale(LC_ALL, $locale, "{$locale}.{$charset}");

        ini_set('log_errors', Env::get('PHP_LOG_ERRORS', 'true'));
        ini_set('error_log', sprintf(Env::get('PHP_ERROR_LOG', Path::storage('/logs/php/%s.log')), date('Y-m-d')));

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });
    }

    public function runningWebserverOrTest(): bool
    {
        return self::runningInWebserver() || self::runningInTest();
    }

    public static function runningInWebserver(): bool
    {
        return !self::runningInConsole();
    }

    public static function runningInConsole(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
    }

    public static function runningInTest(): bool
    {
        return Env::has('PHPUNIT_TEST_SUITE');
    }

    private function registerMiddleware(): void
    {
        $path = $this->pathMiddleware;

        if (is_null($path)) {
            return;
        }

        if (!file_exists($path)) {
            throw new \DomainException("File does not exist in the path [{$path}].");
        }

        $callable = require_once "{$path}";

        if (!is_callable($callable)) {
            throw new \DomainException("The [{$path}] file must return a closure.");
        }

        call_user_func($callable, self::$app);
    }

    private function registerErrorHandler(): void
    {
        $logErrors = Env::get('SLIM_LOG_ERRORS', true);
        $logErrorDetails = Env::get('SLIM_LOG_ERROR_DETAIL', true);
        $displayErrorDetails = Env::get('SLIM_DISPLAY_ERROR_DETAILS', true);

        $serverRequest = ServerRequest::getResolvedInstance();
        $logger = LoggerFacade::getResolvedInstance();

        $httpErrorHandler = new HttpErrorHandler(self::$app->getCallableResolver(), self::$app->getResponseFactory(), $logger);
        $shutdownErrorHandler = new ShutdownErrorHandler($serverRequest, $httpErrorHandler, $displayErrorDetails);
        register_shutdown_function($shutdownErrorHandler);

        $errorMiddleware = self::$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
        $errorMiddleware->setDefaultErrorHandler($httpErrorHandler);

        ini_set('display_errors', 'Off');
        ini_set('display_startup_errors', 'Off');
    }

    private function registerModules(): void
    {
        if (!is_null($this->pathModules) && file_exists($this->pathModules)) {
            $modules = require_once "{$this->pathModules}";

            if (!is_array($modules)) {
                throw new \DomainException(
                    "The [{$this->pathModules}] file must return an array."
                );
            }

            foreach ($modules as $module) {
                if (class_exists($module)) {
                    if (!is_subclass_of($module, Module::class)) {
                        throw new \DomainException(
                            sprintf('Class %s not extends %s', $module, Module::class)
                        );
                    }

                    new $module(self::$app);
                }
            }
        }
    }

    public static function getApp(): App
    {
        if (is_null(self::$app)) {
            throw new \RuntimeException(sprintf('Class %s has not been initialized.', __CLASS__));
        }

        return self::$app;
    }

    public function run(): void
    {
        if (!self::runningInWebserver()) {
            return;
        }

        $response = self::$app->handle(ServerRequest::getResolvedInstance());
        (new ResponseEmitter())->emit($response);
    }
}
