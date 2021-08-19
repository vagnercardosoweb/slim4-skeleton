<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Slim\App;

/**
 * Class Facade.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Facade
{
    /**
     * @var \Slim\App
     */
    protected static App $app;

    /**
     * @var array
     */
    protected static array $resolvedInstance = [];

    /**
     * @var array
     */
    protected static array $aliases = [
        'App' => App::class,
        'Response' => Response::class,
        'Container' => Container::class,
        'ServerRequest' => ServerRequest::class,
    ];

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        $resolvedInstance = static::getResolvedInstance();

        return $resolvedInstance->{$method}(...$arguments);
    }

    /**
     * @param array $aliases
     */
    public static function setAliases(array $aliases): void
    {
        self::$aliases = array_merge(self::$aliases, $aliases);
    }

    /**
     * @param array $aliases
     */
    public static function registerAliases(array $aliases = []): void
    {
        self::setAliases($aliases);

        foreach (self::$aliases as $alias => $class) {
            class_alias($class, $alias, true);
        }
    }

    /**
     * @return mixed
     */
    public static function getResolvedInstance(): mixed
    {
        return static::resolveInstance(static::getAccessor());
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return self::$aliases;
    }

    /**
     * @return \Slim\App
     */
    public static function getApp(): App
    {
        return static::$app;
    }

    /**
     * @param \Slim\App $app
     */
    public static function setApp(App $app): void
    {
        static::$app = $app;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected static function resolveInstance(string $name): mixed
    {
        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        if (!static::$app->getContainer()) {
            return null;
        }

        $container = static::$app->getContainer();

        try {
            return static::$resolvedInstance[$name] = $container->get($name);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "A facade {$name} root has not been set.".
                " {$e->getMessage()}"
            );
        }
    }

    /**
     * @return string
     */
    abstract protected static function getAccessor(): string;
}
