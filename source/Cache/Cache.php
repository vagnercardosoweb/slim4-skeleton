<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Contracts\Cache as ContractCache;
use Predis\Client;

/**
 * Class Cache.
 */
class Cache
{
    /**
     * @var array<string, mixed>
     */
    protected array $drivers = [];

    /**
     * Cache constructor.
     *
     * @param array $config
     */
    public function __construct(protected array $config)
    {
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters = [])
    {
        return $this->resolve()->{$method}(...$parameters);
    }

    /**
     * @param string|null $driver
     *
     * @return \Core\Contracts\Cache
     */
    public function resolve(string|null $driver = null): ContractCache
    {
        $driver = $driver ?? $this->config['default'] ?? 'redis';
        $method = sprintf('create%sDriver', ucfirst($driver));
        $config = $this->getConfigDriver($driver);

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(sprintf(
                'Driver [%s] not supported in cache.',
                $driver
            ));
        }

        if (!empty($this->drivers[$driver])) {
            return $this->drivers[$driver];
        }

        $this->drivers[$driver] = $this->{$method}($config);

        return $this->drivers[$driver];
    }

    /**
     * @param string $driver
     *
     * @return array
     */
    protected function getConfigDriver(string $driver): array
    {
        if (!isset($this->config['drivers'][$driver])) {
            throw new \InvalidArgumentException(sprintf(
                'Driver [%s] does not have the settings defined.',
                $driver
            ));
        }

        return $this->config['drivers'][$driver];
    }

    /**
     * @param array $config
     *
     * @return \Core\Contracts\Cache
     */
    protected function createFileDriver(array $config): ContractCache
    {
        return new FileCache($config['path'], $config['permission']);
    }

    /**
     * @param array $config
     *
     * @return \Core\Contracts\Cache
     */
    protected function createRedisDriver(array $config): ContractCache
    {
        $prefix = $config['prefix'] ?? 'cache:';

        if (!str_ends_with($prefix, ':')) {
            $prefix = sprintf('%s:', $prefix);
        }

        return new RedisCache(new Client($config, ['prefix' => $prefix]));
    }
}
