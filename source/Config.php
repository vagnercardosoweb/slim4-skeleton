<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/01/2022 Vagner Cardoso
 */

namespace Core;

use Core\Support\Arr;
use Core\Support\Common;
use Core\Support\Path;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class Config.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Config
{
    /**
     * @var array
     */
    protected static array $items = [];

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function prepend(string $key, mixed $value)
    {
        $array = self::get($key);
        array_unshift($array, $value);

        self::set($key, $array);
    }

    /**
     * @param array|string $key
     * @param mixed        $default
     *
     * @return mixed
     */
    public static function get(array | string $key, mixed $default = null): mixed
    {
        if (empty(self::$items)) {
            self::loadItems();
        }

        if (is_array($key)) {
            return self::getMany($key);
        }

        return Arr::get(self::$items, $key, $default);
    }

    /**
     * @param string|null $path
     *
     * @return array
     */
    public static function loadItems(string $path = null): array
    {
        if (!is_dir($path)) {
            $path = Path::config();
        }

        /** @var \DirectoryIterator $iterator */
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        $iterator->rewind();

        $config = [];

        while ($iterator->valid()) {
            $directory = $iterator->getPath();
            $fileBasename = $iterator->getBasename('.php');

            if ($directory = trim(str_replace($path, '', $directory), DIRECTORY_SEPARATOR)) {
                $directory = sprintf('%s%s', $directory, DIRECTORY_SEPARATOR);
            }

            if (str_contains($directory, DIRECTORY_SEPARATOR)) {
                foreach (explode(DIRECTORY_SEPARATOR, $directory) as $segment) {
                    if (empty($segment) || !is_dir("{$path}/{$segment}")) {
                        continue;
                    }

                    $config[$segment] = self::loadItems("{$path}/{$segment}");
                }
            } else {
                $config[$fileBasename] = require "{$iterator->getRealPath()}";
            }

            $iterator->next();
        }

        ksort($config, SORT_NATURAL);

        self::$items = self::normalize($config);

        return self::$items;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected static function normalize(array $config): array
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = self::normalize($value);
            } else {
                $config[$key] = Common::normalizeValue($value);
            }
        }

        return $config;
    }

    /**
     * @param array $keys
     *
     * @return array
     */
    public static function getMany(array $keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                throw new \UnexpectedValueException('the key must be a string');
            }

            $config[$key] = Arr::get(self::$items, $key, $default);
        }

        return $config;
    }

    /**
     * @param array|string $key
     * @param mixed|null   $value
     *
     * @return void
     */
    public static function set(array | string $key, mixed $value = null)
    {
        if (empty(self::$items)) {
            self::loadItems();
        }

        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set(self::$items, $key, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function push(string $key, mixed $value)
    {
        $array = self::get($key);
        $array[] = $value;

        self::set($key, $array);
    }

    /**
     * @param string     $key
     * @param mixed      $value
     * @param mixed|null $newKey
     *
     * @return void
     */
    public static function add(string $key, mixed $value, mixed $newKey)
    {
        $array = self::get($key);
        $array[$newKey] = $value;

        self::set($key, $array);
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        if (empty(self::$items)) {
            self::loadItems();
        }

        return self::$items;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        if (empty(self::$items)) {
            self::loadItems();
        }

        return Arr::has(self::$items, $key);
    }
}
