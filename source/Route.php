<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace Core;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;

/**
 * Class Route.
 *
 * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Route
{
    /**
     * @var string
     */
    protected static string $namespace = 'App\\Controllers';

    /**
     * @var string
     */
    protected static string $groupPattern = '';

    /**
     * @var \Slim\Interfaces\RouteCollectorProxyInterface
     */
    protected static RouteCollectorProxyInterface $routeCollectorProxy;

    /**
     * @param \Slim\Interfaces\RouteCollectorProxyInterface $routeCollectorProxy
     */
    public static function setRouteCollectorProxy(RouteCollectorProxyInterface $routeCollectorProxy): void
    {
        self::$routeCollectorProxy = $routeCollectorProxy;
    }

    /**
     * @param string          $pattern
     * @param string|\Closure $callable
     * @param string|null     $name
     * @param array           $middlewares
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function get(string $pattern, string|\Closure $callable, ?string $name = null, array $middlewares = []): RouteInterface
    {
        return self::route(['get'], $pattern, $callable, $name, $middlewares);
    }

    /**
     * @param array           $methods
     * @param string          $pattern
     * @param string|\Closure $callable
     * @param string|null     $name
     * @param array           $middlewares
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function route(
        array $methods,
        string $pattern,
        string|\Closure $callable,
        ?string $name = null,
        array $middlewares = []
    ): RouteInterface {
        $name = self::validateRouteName($name);
        $methods = array_map(fn (string $method) => strtoupper($method), $methods);

        $pattern = self::$groupPattern.$pattern;
        $route = self::$routeCollectorProxy->map($methods, $pattern, self::handleCallableRouter($callable));

        if (!empty($name)) {
            $route->setName(strtolower($name));
        }

        foreach ($middlewares as $middleware) {
            $route->add($middleware);
        }

        return $route;
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    private static function validateRouteName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        $name = mb_strtolower($name);
        $routes = self::$routeCollectorProxy->getRouteCollector()->getRoutes();

        foreach ($routes as $route) {
            if ($route->getName() === $name) {
                throw new \LogicException(
                    "There are registered routes with the same name [{$name}]."
                );
            }
        }

        return $name;
    }

    /**
     * @param string|callable $callable
     *
     * @return \Closure
     */
    private static function handleCallableRouter(callable|string $callable): \Closure
    {
        $namespace = self::$namespace;

        return function (ServerRequestInterface $request, ResponseInterface $response, array $params) use ($callable, $namespace) {
            if (is_callable($callable)) {
                $result = $callable($request, $response, ...array_values($params));
            } else {
                list($name, $originalMethod) = (explode('@', $callable) + [1 => '']);

                $method = mb_strtolower($request->getMethod()).ucfirst($originalMethod);
                $namespace = sprintf('%s\%s', $namespace, $name);
                $controller = new $namespace($this);

                if (!method_exists($controller, $method)) {
                    $method = $originalMethod ?: 'index';
                }

                $result = call_user_func_array([$controller, $method], array_values($params));
                $response = $controller->getResponse();
            }

            if ($result instanceof ResponseInterface) {
                return $result;
            }

            if (is_array($result) || is_object($result)) {
                $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
                $result = json_encode($result, JSON_THROW_ON_ERROR);
            }

            if (!$result instanceof ResponseInterface) {
                $response->getBody()->write((string)$result);

                return $response;
            }

            return $result;
        };
    }

    /**
     * @param string          $pattern
     * @param string|\Closure $callable
     * @param string|null     $name
     * @param array           $middlewares
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function post(string $pattern, string|\Closure $callable, ?string $name = null, array $middlewares = []): RouteInterface
    {
        return self::route(['post'], $pattern, $callable, $name, $middlewares);
    }

    /**
     * @param string          $pattern
     * @param string|\Closure $callable
     * @param string|null     $name
     * @param array           $middlewares
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function put(string $pattern, string|\Closure $callable, ?string $name = null, array $middlewares = []): RouteInterface
    {
        return self::route(['put'], $pattern, $callable, $name, $middlewares);
    }

    /**
     * @param string          $pattern
     * @param string|\Closure $callable
     * @param string|null     $name
     * @param array           $middlewares
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function delete(string $pattern, string|\Closure $callable, ?string $name = null, array $middlewares = []): RouteInterface
    {
        return self::route(['delete'], $pattern, $callable, $name, $middlewares);
    }

    /**
     * @param string          $pattern
     * @param string|\Closure $callable
     * @param string|null     $name
     * @param array           $middlewares
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function patch(string $pattern, string|\Closure $callable, ?string $name = null, array $middlewares = []): RouteInterface
    {
        return self::route(['patch'], $pattern, $callable, $name, $middlewares);
    }

    /**
     * @return void
     */
    public static function enableOptions(): void
    {
        self::$routeCollectorProxy->options('/{routes:.*}', function ($request, ResponseInterface $response) {
            return $response->withStatus(StatusCodeInterface::STATUS_OK);
        });
    }

    /**
     * @param string|array $pattern
     * @param \Closure     $callable
     * @param array        $middlewares
     *
     * @return \Slim\Interfaces\RouteGroupInterface
     */
    public static function group(string|array $pattern, \Closure $callable, array $middlewares = []): RouteGroupInterface
    {
        $namespace = null;

        if (is_array($pattern)) {
            $namespace = rtrim(ltrim($pattern['namespace'] ?? '', '\/'), '\/');
            $middlewares = array_merge($middlewares, $pattern['middlewares'] ?? []);
            $pattern = $pattern['pattern'] ?? '';
        }

        $currentDefaultNamespace = self::$namespace;

        if (!empty($namespace)) {
            self::setNamespace($namespace);
        }

        $currentGroupPattern = self::$groupPattern;
        $pattern = $currentGroupPattern.$pattern;
        self::$groupPattern = $pattern;

        $group = self::$routeCollectorProxy->group($pattern, $callable);

        foreach ($middlewares as $middleware) {
            $group->add($middleware);
        }

        self::$groupPattern = $currentGroupPattern;
        self::$namespace = $currentDefaultNamespace;

        return $group;
    }

    /**
     * @param string $namespace
     */
    public static function setNamespace(string $namespace): void
    {
        $namespace = self::normalizeNamespace($namespace);

        self::$namespace = $namespace;
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    protected static function normalizeNamespace(string $namespace): string
    {
        return str_ireplace('/', '\\', $namespace);
    }

    /**
     * @param string $basePath
     */
    public static function setBasePath(string $basePath): void
    {
        self::$routeCollectorProxy->setBasePath($basePath);
    }

    /**
     * @param string                                $from
     * @param \Psr\Http\Message\UriInterface|string $to
     * @param int                                   $status
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function redirect(string $from, UriInterface|string $to, int $status = 302): RouteInterface
    {
        return self::$routeCollectorProxy->redirect($from, $to, $status);
    }

    /**
     * @param string $folder
     */
    public static function registerFolder(string $folder): void
    {
        /** @var \DirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $folder, \FilesystemIterator::SKIP_DOTS
            )
        );

        $iterator->rewind();

        while ($iterator->valid()) {
            if ('php' === $iterator->getExtension()) {
                self::registerPath($iterator->getRealPath());
            }

            $iterator->next();
        }
    }

    /**
     * @param string $path
     */
    public static function registerPath(string $path): void
    {
        if (file_exists($path) && !is_dir($path)) {
            require "{$path}";

            return;
        }

        if (is_dir($path)) {
            self::registerFolder($path);
        } else {
            throw new \DomainException("Path [{$path}] of routes not found.");
        }
    }
}
