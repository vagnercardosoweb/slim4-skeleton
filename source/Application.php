<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

declare(strict_types=1);

namespace Core;

use Core\Facades\Facade;
use Core\Facades\ServerRequest;
use Core\Interfaces\SessionInterface;
use Core\Support\Env;
use Core\Support\Path;
use Core\Support\Str;
use DI\Container;
use DI\ContainerBuilder;
use DomainException;
use ErrorException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use RuntimeException;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Slim\Psr7\Factory\UriFactory;
use Throwable;

class Application
{
    public const VERSION = '1.0.0';
    protected static App|null $instance = null;

    /**
     * @param string $pathRoutes
     * @param string $pathMiddleware
     * @param string $pathProviders
     * @param bool $immutableEnv
     *
     * @throws Throwable
     */
    public function __construct(
        protected string $pathRoutes,
        protected string $pathMiddleware,
        protected string $pathProviders,
        protected bool   $immutableEnv = false
    )
    {
        $this->initializeEnvironment();

        $this->registerApp();
        $this->registerFacade();
        $this->registerPhpSettings();

        if ($this->runningWebserverOrTest()) {
            $this->registerMiddleware();

            Route::setRouteCollectorProxy(self::$instance);

            if ($this->pathRoutes) {
                Route::registerPath($this->pathRoutes);
            }
        }
    }

    protected function initializeEnvironment(): void
    {
        Env::load($this->immutableEnv);

        foreach (['APP_KEY', 'API_SECRET_KEY', 'DEPLOY_SECRET_KEY'] as $key) {
            $value = Env::get($key);
            if (!empty($value)) continue;

            $quote = preg_quote("={$value}", '/');
            $random = Str::randomHexBytes();
            $pathEnv = Env::path();

            file_put_contents(
                $pathEnv,
                preg_replace(
                    "/^{$key}{$quote}.*/m",
                    "{$key}=vcw:{$random}",
                    file_get_contents($pathEnv)
                )
            );
        }
    }

    /**
     * @return void
     * @throws Throwable
     *
     */
    protected function registerApp(): void
    {
        if (is_null(self::$instance)) {
            $container = $this->registerContainerBuilder();
            self::$instance = $container->get(App::class);
        }
    }

    /**
     * @return Container
     * @throws Throwable
     *
     */
    protected function registerContainerBuilder(): Container
    {
        $definitions = file_exists((string)$this->pathProviders) ? require_once "{$this->pathProviders}" : [];
        $container = new ContainerBuilder();

        if (Env::get('CONTAINER_CACHE', false)) {
            $container->enableCompilation(Path::storage('/cache/container'));
        }

        $container->useAutowiring(Env::get('CONTAINER_AUTO_WIRING', true));

        $container->addDefinitions(array_merge([
            App::class => function (ContainerInterface $container) {
                AppFactory::setContainer($container);
                $container->get(SessionInterface::class);

                return AppFactory::create();
            },

            SessionInterface::class => fn() => new Session(),
            StreamFactoryInterface::class => fn() => new StreamFactory(),
            UploadedFileFactoryInterface::class => fn() => new UploadedFileFactory(),
            UriFactoryInterface::class => fn() => new UriFactory(),

            ServerRequestFactoryInterface::class => function (ContainerInterface $container) {
                return new ServerRequestFactory(
                    $container->get(StreamFactoryInterface::class),
                    $container->get(UriFactoryInterface::class)
                );
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
        ], $definitions));

        return $container->build();
    }

    protected function registerFacade(): void
    {
        Facade::setApp(self::$instance);
        Facade::registerAliases();
    }

    /**
     * @return void
     * @throws Throwable
     *
     */
    protected function registerPhpSettings(): void
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
                throw new ErrorException($message, 0, $level, $file, $line);
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

    protected function registerMiddleware(): void
    {
        $path = $this->pathMiddleware;

        if (!is_callable($callable = require_once "{$path}")) {
            throw new DomainException("The [{$path}] file must return a callable.");
        }

        call_user_func($callable, self::$instance);
    }

    public static function getInstance(): App
    {
        if (is_null(self::$instance)) {
            throw new RuntimeException(sprintf(
                'Class %s has not been initialized.',
                __CLASS__
            ));
        }

        return self::$instance;
    }

    public function run(): void
    {
        if (!self::runningInWebserver()) {
            return;
        }

        $response = self::$instance->handle(ServerRequest::getResolvedInstance());
        (new ResponseEmitter())->emit($response);
        exit;
    }
}
