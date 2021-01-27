<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/01/2021 Vagner Cardoso
 */

namespace Core\Helpers;

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
     * @var \Dotenv\Repository\RepositoryInterface|null
     */
    protected static ?RepositoryInterface $repository = null;

    /**
     * @param string $name
     *
     * @return array|null
     */
    public static function initialize(string $name = '.env'): ?array
    {
        $envPath = dirname(self::path());

        return Dotenv::create(
            self::repository(),
            $envPath,
            $name
        )->load();
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!$value = static::repository()->get($key)) {
            return $default;
        }

        $value = Helper::normalizeValue($value);

        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return is_string($value) ? trim($value) : $value;
    }

    /**
     * @return string
     */
    public static function path(): string
    {
        $env = Path::app('/.env');
        $example = Path::app('/.env.example');

        if (!file_exists($env) && file_exists($example)) {
            file_put_contents($env, file_get_contents($example));
        }

        return $env;
    }

    /**
     * @return string[]
     */
    protected static function adapters(): array
    {
        return [
            ApacheAdapter::class,
            EnvConstAdapter::class,
            ServerConstAdapter::class,
            PutenvAdapter::class,
        ];
    }

    /**
     * @return \Dotenv\Repository\RepositoryInterface
     */
    protected static function repository(): RepositoryInterface
    {
        if (null === static::$repository) {
            $repository = RepositoryBuilder::createWithNoAdapters();

            foreach (static::adapters() as $adapter) {
                $repository = $repository->addWriter($adapter);
                $repository = $repository->addAdapter($adapter);
            }

            static::$repository = $repository->immutable()->make();
        }

        return static::$repository;
    }
}
