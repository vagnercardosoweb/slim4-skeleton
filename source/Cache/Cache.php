<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/03/2021 Vagner Cardoso
 */

namespace Core\Cache;

use Core\Interfaces\CacheStore;
use Core\Redis;

/**
 * Class Cache.
 *
 * @method mixed get(string $key, $default = null, int $seconds = null)
 * @method bool set(string $key, $value, int $seconds = 1)
 * @method bool flush()
 * @method bool has(string $key)
 * @method bool increment(string $key, $value = 1)
 * @method bool decrement(string $key, $value = 1)
 * @method bool delete($key)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Cache
{
    /**
     * @var array|string
     */
    protected $config;

    /**
     * @var array
     */
    protected $stores = [];

    /**
     * Cache constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        return $this->store()->{$method}(...$arguments);
    }

    /**
     * @param string|null $driver
     *
     * @return \Core\Interfaces\CacheStore
     */
    public function store(string $driver = null): CacheStore
    {
        if (is_null($driver)) {
            $driver = $this->getDefaultDriver();
        }

        $config = $this->getStoreConfig($driver);
        $method = sprintf('create%sDriver', ucfirst($driver));

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException(
                "Driver [{$driver}] not supported in cache."
            );
        }

        if (!$this->stores[$driver] instanceof CacheStore) {
            $this->stores[$driver] = $this->{$method}($config);
        }

        return $this->stores[$driver];
    }

    /**
     * @param string $driver
     *
     * @return array
     */
    protected function getStoreConfig(string $driver): array
    {
        if (!isset($this->config['stores'][$driver])) {
            throw new \InvalidArgumentException(
                "Driver [{$driver}] does not have the settings defined."
            );
        }

        return $this->config['stores'][$driver];
    }

    /**
     * @return string
     */
    protected function getDefaultDriver(): string
    {
        return $this->config['default'] ?? 'file';
    }

    /**
     * @param array $config
     *
     * @return \Core\Interfaces\CacheStore
     */
    protected function createFileDriver(array $config): CacheStore
    {
        return new FileStore($config['path'], $config['permission'] ?? null);
    }

    /**
     * @param array $config
     *
     * @return \Core\Interfaces\CacheStore
     */
    protected function createRedisDriver(array $config): CacheStore
    {
        $client = new Redis($config, [
            'prefix' => $config['prefix'] ?? 'cache:',
        ]);

        return new RedisStore($client);
    }

    /**
     * @param array $config
     *
     * @return \Core\Interfaces\CacheStore
     */
    protected function createApcDriver(array $config): CacheStore
    {
        return new ApcStore($config['prefix'] ?? 'cache:');
    }
}
