<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace Core\Support;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\ApacheAdapter;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\Adapter\ServerConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;

/**
 * Class Env.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Env
{
    /**
     * @var bool
     */
    protected static bool $immutable = false;

    /**
     * @var \Dotenv\Repository\RepositoryInterface|null
     */
    protected static ?RepositoryInterface $repository = null;

    /**
     * @var array
     */
    protected static array $environments = [];

    /**
     * @var string[]
     */
    protected static array $adapters = [
        ApacheAdapter::class,
        EnvConstAdapter::class,
        ServerConstAdapter::class,
        PutenvAdapter::class,
    ];

    /**
     * @return array
     */
    public static function all(): array
    {
        return self::$environments;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public static function set(string $name, string $value): void
    {
        if (!self::$immutable) {
            self::$environments[$name] = $value;
        }

        self::repository()->set($name, $value);
    }

    /**
     * @return \Dotenv\Repository\RepositoryInterface
     */
    protected static function repository(): RepositoryInterface
    {
        if (null === self::$repository) {
            $repository = RepositoryBuilder::createWithNoAdapters();

            foreach (self::$adapters as $adapter) {
                $repository = $repository->addWriter($adapter);
                $repository = $repository->addAdapter($adapter);
            }

            if (self::$immutable) {
                self::$repository = $repository->immutable()->make();
            } else {
                self::$repository = $repository->make();
            }
        }

        return self::$repository;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function has(string $name): bool
    {
        return self::repository()->has($name);
    }

    /**
     * @param bool $immutable
     *
     * @return \Dotenv\Dotenv
     */
    public static function load(bool $immutable = false): Dotenv
    {
        self::$immutable = $immutable;
        $envPathInfo = pathinfo(self::path());

        if (!$immutable) {
            $dotenv = Dotenv::create(self::repository(), $envPathInfo['dirname'], $envPathInfo['basename']);
        } else {
            $dotenv = Dotenv::createImmutable($envPathInfo['dirname'], $envPathInfo['basename']);
        }

        self::$environments = $dotenv->load();

        return $dotenv;
    }

    /**
     * @return string
     */
    public static function path(): string
    {
        $envFile = $_ENV['PHPUNIT_TEST_SUITE'] ?? false ? '.testing' : '';

        $env = Path::app("/.env{$envFile}");
        $example = Path::app('/.env.example');

        if (!file_exists($env) && file_exists($example)) {
            file_put_contents($env, file_get_contents($example));
        }

        return $env;
    }

    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public static function get(string $name, mixed $default = null): mixed
    {
        if (!$value = self::repository()->get($name)) {
            return $default;
        }

        $value = Common::normalizeValue($value);

        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return is_string($value) ? trim($value) : $value;
    }
}
