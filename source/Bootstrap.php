<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 21/02/2021 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core;

use Core\Facades\Facade;
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
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\ResponseEmitter;

/**
 * Class Bootstrap.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Bootstrap
{
    /**
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * @var \Slim\App|null
     */
    private static ?App $app = null;

    /**
     * App constructor.
     *
     * @param string|null $registerRoutePath
     * @param string|null $registerMiddlewarePath
     * @param string|null $registerContainerPath
     * @param bool|null   $immutableEnv
     */
    public function __construct(
        private ?string $registerRoutePath = null,
        private ?string $registerMiddlewarePath = null,
        private ?string $registerContainerPath = null,
        private ?bool $immutableEnv = false
    ) {
        Env::load($immutableEnv);

        $this->registerApp();
        $this->registerFacade();

        if ($this->runningWebserverOrTest()) {
            $this->registerMiddleware();
        }

        $this->registerErrorHandler();
        $this->registerPhpSettings();

        if ($this->runningWebserverOrTest() && $registerRoutePath) {
            Route::setRouteCollectorProxy(self::$app);
            Route::registerPath($registerRoutePath);
        }
    }

    /**
     * @return App
     */
    private function registerApp(): App
    {
        if (is_null(self::$app)) {
            $container = $this->registerContainerBuilder();
            self::$app = $container->get(App::class);
        }

        return self::$app;
    }

    /**
     * @return \DI\Container
     */
    private function registerContainerBuilder(): Container
    {
        $container = [];
        $containerPath = (string)$this->registerContainerPath;
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

    /**
     * @return void
     */
    private function registerFacade(): void
    {
        Facade::setApp(self::$app);
        Facade::registerAliases();
    }

    /**
     * @return void
     */
    private function registerMiddleware(): void
    {
        $path = $this->registerMiddlewarePath;

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

    /**
     * @return void
     */
    private function registerErrorHandler(): void
    {
        error_reporting(-1);

        set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });

        $serverRequest = ServerRequest::getResolvedInstance();
        $logErrors = Env::get('SLIM_LOG_ERRORS', true);
        $logErrorDetails = Env::get('SLIM_LOG_ERROR_DETAIL', true);
        $displayErrorDetails = Env::get('SLIM_DISPLAY_ERROR_DETAILS', true);

        $httpErrorHandler = new HttpErrorHandler();
        $shutdownErrorHandler = new ShutdownErrorHandler($serverRequest, $httpErrorHandler, $displayErrorDetails);
        register_shutdown_function($shutdownErrorHandler);

        $errorMiddleware = self::$app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
        $errorMiddleware->setDefaultErrorHandler($httpErrorHandler);

        ini_set('display_errors', 'Off');
        ini_set('display_startup_errors', 'Off');
    }

    /**
     * @return void
     */
    private function registerPhpSettings(): void
    {
        $locale = Env::get('APP_LOCALE', 'pt_BR');
        $charset = Env::get('APP_CHARSET', 'UTF-8');

        ini_set('default_charset', $charset);
        date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Sao_Paulo'));
        mb_internal_encoding($charset);
        setlocale(LC_ALL, $locale, "{$locale}.{$charset}");

        ini_set('log_errors', Env::get('PHP_LOG_ERRORS', 'true'));
        ini_set('error_log', sprintf(Env::get('PHP_ERROR_LOG', Path::storage('/logs/php/%s.log')), date('Y-m-d')));
    }

    /**
     * @return \Slim\App
     */
    public static function getApp(): App
    {
        if (is_null(self::$app)) {
            throw new \RuntimeException(sprintf('Class %s has not been initialized.', __CLASS__));
        }

        return self::$app;
    }

    /**
     * @return bool
     */
    public static function runningInConsole(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
    }

    /**
     * @return bool
     */
    public static function runningInTest(): bool
    {
        return Env::has('PHPUNIT_TEST_SUITE');
    }

    /**
     * @return bool
     */
    public static function runningInWebserver(): bool
    {
        return !self::runningInConsole();
    }

    /**
     * @return bool
     */
    public function runningWebserverOrTest(): bool
    {
        return self::runningInWebserver() || self::runningInTest();
    }

    /**
     * @return void
     */
    public function run(): void
    {
        if (!self::runningInWebserver()) {
            return;
        }

        $response = self::$app->handle(ServerRequest::getResolvedInstance());
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }
}
