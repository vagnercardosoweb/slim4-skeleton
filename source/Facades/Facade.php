<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace Core\Facades;

use Slim\App;

abstract class Facade
{
    protected static App $app;

    /**
     * @var array<string, mixed>
     */
    protected static array $resolvedInstance = [];

    /**
     * @var array<string, string>
     */
    protected static array $aliases = [
        'App' => App::class,
        'Response' => Response::class,
        'ServerRequest' => ServerRequest::class,
        'Container' => Container::class,
    ];

    public static function __callStatic(string $method, array $arguments): mixed
    {
        $resolvedInstance = self::getResolvedInstance();

        return $resolvedInstance->{$method}(...$arguments);
    }

    public static function getResolvedInstance(): mixed
    {
        return self::resolveInstance(static::getAccessor());
    }

    protected static function resolveInstance(string $name): mixed
    {
        if (isset(self::$resolvedInstance[$name])) {
            return self::$resolvedInstance[$name];
        }

        if (!self::$app->getContainer()) {
            return null;
        }

        $container = self::$app->getContainer();

        try {
            return self::$resolvedInstance[$name] = $container->get($name);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf(
                'A facade [%s] root has not been set. %s',
                $name,
                $e->getMessage()
            )
            );
        }
    }

    abstract protected static function getAccessor(): string;

    public static function registerAliases(array $aliases = []): void
    {
        self::setAliases($aliases);

        foreach (self::$aliases as $alias => $class) {
            class_alias($class, $alias, true);
        }
    }

    public static function getAliases(): array
    {
        return self::$aliases;
    }

    public static function setAliases(array $aliases): void
    {
        self::$aliases = array_merge(self::$aliases, $aliases);
    }

    public static function getApp(): App
    {
        return self::$app;
    }

    public static function setApp(App $app): void
    {
        self::$app = $app;
    }
}
