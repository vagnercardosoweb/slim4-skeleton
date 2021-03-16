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

/**
 * Class ApcStore.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ApcStore implements CacheStore
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var bool
     */
    protected $apcu;

    /**
     * ApcStore constructor.
     *
     * @param string $prefix
     */
    public function __construct(string $prefix = null)
    {
        $this->prefix = $prefix;
        $this->apcu = function_exists('apcu_fetch');

        if (!$this->apcu && !function_exists('apc_fetch')) {
            throw new \UnexpectedValueException(
                'Install php extension [ext-apcu].'
            );
        }
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @param int    $seconds
     *
     * @return mixed
     */
    public function get(string $key, $default = null, int $seconds = 0)
    {
        $nKey = $this->generateKey($key);
        $value = $this->apcu ? apcu_fetch($nKey) : apc_fetch($nKey);

        if (empty($value)) {
            $value = $default instanceof \Closure ? $default() : $default;

            if ($seconds > 0) {
                $this->set($key, $value, $seconds);
            }
        }

        return $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $seconds
     *
     * @return mixed
     */
    public function set(string $key, $value, int $seconds = 0): bool
    {
        $key = $this->generateKey($key);

        return $this->apcu ? apcu_store($key, $value, $seconds) : apc_store($key, $value, $seconds);
    }

    /**
     * @return bool
     */
    public function flush(): bool
    {
        return $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        $key = $this->generateKey($key);

        return $this->apcu ? apcu_exists($key) : apc_exists($key);
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function increment(string $key, $value = 1): bool
    {
        $key = $this->generateKey($key);

        return $this->apcu ? apcu_inc($key, $value) : apc_inc($key, $value);
    }

    /**
     * @param string $key
     * @param int    $value
     *
     * @return mixed
     */
    public function decrement(string $key, $value = 1): bool
    {
        $key = $this->generateKey($key);

        return $this->apcu ? apcu_dec($key, $value) : apc_dec($key, $value);
    }

    /**
     * @param string|array $key
     *
     * @return bool
     */
    public function delete($key): bool
    {
        $key = $this->generateKey($key);

        return $this->apcu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function generateKey(string $key): string
    {
        return sprintf('%s%s', $this->prefix, $key);
    }
}
