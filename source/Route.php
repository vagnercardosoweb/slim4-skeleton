<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace Core;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
    protected static string $defaultNamespace = 'App\\Controllers';

    /**
     * @var string
     */
    protected static string $groupPattern = '';

    /**
     * @var \Slim\Interfaces\RouteCollectorProxyInterface
     */
    protected static RouteCollectorProxyInterface $routeCollectorProxy;

    /**
     * @param string $groupPattern
     */
    public static function setGroupPattern(string $groupPattern): void
    {
        self::$groupPattern = $groupPattern;
    }

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
        return self::route('get', $pattern, $callable, $name, $middlewares);
    }

    /**
     * @param string|array    $methods
     * @param string          $pattern
     * @param string|\Closure $callable
     * @param string|null     $name
     * @param array           $middlewares
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function route(
        array|string $methods,
        string $pattern,
        string|\Closure $callable,
        ?string $name = null,
        array $middlewares = []
    ): RouteInterface {
        $name = self::validateRouteName($name);
        $methods = '*' == $methods ? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] : $methods;
        $methods = (is_string($methods) ? explode(',', mb_strtoupper($methods)) : $methods);
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
        $namespace = self::$defaultNamespace;

        return function (ServerRequestInterface $request, ResponseInterface $response, array $params) use ($callable, $namespace) {
            if (is_callable($callable)) {
                $result = $callable($request, $response, ...array_values($params));
            } else {
                list($name, $originalMethod) = (explode('@', $callable) + [1 => '']);

                $method = mb_strtolower($request->getMethod()).ucfirst($originalMethod);
                $namespace = sprintf('%s\%s', $namespace, $name);

                $controller = new $namespace($request, $response, $this);

                if (!method_exists($controller, $method)) {
                    $method = $originalMethod ?: 'index';

                    if (!method_exists($controller, $method)) {
                        throw new \BadMethodCallException(
                            sprintf('Call to undefined method %s::%s()', get_class($controller), $method)
                        );
                    }
                }

                $result = call_user_func_array([$controller, $method], array_values($params));
                $response = $controller->getResponse();
            }

            if ((is_array($result) || is_object($result)) && !$result instanceof ResponseInterface) {
                $response = $response->withHeader('Content-Type', 'application/json');
                $result = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
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
        return self::route('post', $pattern, $callable, $name, $middlewares);
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
        return self::route('put', $pattern, $callable, $name, $middlewares);
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
        return self::route('delete', $pattern, $callable, $name, $middlewares);
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
        return self::route('patch', $pattern, $callable, $name, $middlewares);
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
        $resetNamespace = false;

        if (is_array($pattern)) {
            $namespace = rtrim(ltrim($pattern['namespace'] ?? '', '\/'), '\/');
            $resetNamespace = $pattern['resetNamespace'] ?? false;
            $middlewares = array_merge($middlewares, $pattern['middlewares'] ?? []);
            $pattern = $pattern['pattern'] ?? '';
        }

        $currentDefaultNamespace = self::$defaultNamespace;

        if (!empty($namespace)) {
            if (!$resetNamespace) {
                $namespace = $currentDefaultNamespace.$namespace;
            }

            self::setDefaultNamespace($namespace);
        }

        $currentGroupPattern = self::$groupPattern;
        $pattern = $currentGroupPattern.$pattern;
        self::$groupPattern = $pattern;

        $group = self::$routeCollectorProxy->group($pattern, $callable);

        foreach ($middlewares as $middleware) {
            $group->add($middleware);
        }

        self::$groupPattern = $currentGroupPattern;
        self::$defaultNamespace = $currentDefaultNamespace;

        return $group;
    }

    /**
     * @param string $defaultNamespace
     */
    public static function setDefaultNamespace(string $defaultNamespace): void
    {
        $defaultNamespace = self::normalizeNamespace($defaultNamespace);

        self::$defaultNamespace = $defaultNamespace;
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
     * @param string $from
     * @param        $to
     * @param int    $status
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public static function redirect(string $from, $to, int $status = 302): RouteInterface
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
