<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace Core;

/**
 * Class EventEmitter.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class EventEmitter
{
    /**
     * @var array<string, array<int, callable>>
     */
    protected static array $listeners = [];

    /**
     * @param string   $eventName
     * @param callable $callable
     */
    public static function on(string $eventName, callable $callable): void
    {
        if (!isset(self::$listeners[$eventName])) {
            self::$listeners[$eventName] = [];
        }

        self::$listeners[$eventName][] = $callable;
    }

    /**
     * @param string $eventName
     *
     * @return array<int, mixed>
     */
    public static function emit(string $eventName): array
    {
        $result = [];

        if (!empty(self::$listeners[$eventName])) {
            if (count(self::$listeners[$eventName]) > 0) {
                ksort(self::$listeners[$eventName]);
            }

            $arguments = func_get_args();
            array_shift($arguments);

            foreach (self::$listeners[$eventName] as $key => $callable) {
                $result[$key] = call_user_func_array($callable, $arguments);
            }
        }

        return $result;
    }

    /**
     * @return \callable[][]
     */
    public static function all(): array
    {
        return self::$listeners;
    }

    /**
     * @param string   $eventName
     * @param int|null $index
     */
    public static function remove(string $eventName, ?int $index = null): void
    {
        if (empty(self::$listeners[$eventName])) {
            return;
        }

        if (!is_null($index)) {
            unset(self::$listeners[$eventName][$index]);
        } else {
            self::$listeners[$eventName] = [];
        }

        if (count(self::$listeners[$eventName]) <= 0) {
            unset(self::$listeners[$eventName]);
        }
    }
}
