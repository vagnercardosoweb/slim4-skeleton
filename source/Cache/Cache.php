<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
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
     * @var array
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
     * @param string|null $driver
     *
     * @return \Core\Contracts\Cache
     */
    public function resolve(?string $driver = null): ContractCache
    {
        $driver = $driver ?? $this->getDefaultDriver();
        $configDriver = $this->getConfigDriver($driver);
        $parseMethod = sprintf('create%sDriver', ucfirst($driver));

        if (!method_exists($this, $parseMethod)) {
            throw new \InvalidArgumentException(
                "Driver [{$driver}] not supported in cache."
            );
        }

        if (!empty($this->drivers[$driver]) && $this->drivers[$driver] instanceof ContractCache) {
            return $this->drivers[$driver];
        }

        $this->drivers[$driver] = $this->{$parseMethod}($configDriver);

        return $this->drivers[$driver];
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
        $prefix = $config['prefix'] ?? null;

        return new RedisCache(new Client($config, ['prefix' => $prefix]), $prefix);
    }

    /**
     * @param string $driver
     *
     * @return array
     */
    protected function getConfigDriver(string $driver): array
    {
        if (!isset($this->config['drivers'][$driver])) {
            throw new \InvalidArgumentException(
                "Driver [{$driver}] does not have the settings defined."
            );
        }

        return $this->config['drivers'][$driver];
    }

    /**
     * @return string
     */
    protected function getDefaultDriver(): string
    {
        return $this->config['default'] ?? 'redis';
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
}
