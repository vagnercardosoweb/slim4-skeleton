<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Obj.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Obj
{
    /**
     * @param object|array|null $array
     *
     * @return object|null
     */
    public static function fromArray(object|array|null $array): ?object
    {
        if (is_object($array)) {
            return $array;
        }

        $object = new \stdClass();

        if (!is_array($array)) {
            return $object;
        }

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $value = self::fromArray($value);
            }

            $object->{$key} = $value ?? null;
        }

        return $object;
    }

    /**
     * @param object      $object
     * @param string|null $name
     * @param mixed       $default
     *
     * @return mixed
     */
    public static function get(object $object, ?string $name = null, $default = null): mixed
    {
        if (empty($name)) {
            return $object;
        }

        foreach (explode('.', $name) as $segment) {
            if (!is_object($object) || !isset($object->{$segment})) {
                return $default;
            }

            $object = $object->{$segment};
        }

        return $object;
    }

    /**
     * @param mixed        $object
     * @param string|array $methods
     *
     * @return bool
     */
    public static function checkMethodExists(mixed $object, array|string $methods): bool
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as $method) {
            if (!empty($method) && method_exists($object, $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param object|array $object
     *
     * @return string
     */
    public static function toJson(object|array $object): string
    {
        return json_encode(self::toArray($object), JSON_UNESCAPED_SLASHES, JSON_PRETTY_PRINT);
    }

    /**
     * @param object|array|null $object
     *
     * @return array|null
     */
    public static function toArray(object|array|null $object): ?array
    {
        $array = [];

        if (!is_object($object) && !is_array($object)) {
            return $array;
        }

        foreach ($object as $key => $value) {
            if ($value instanceof \SimpleXMLElement) {
                $value = strval($value);
            }

            if (is_object($value) || is_array($value)) {
                if ($value instanceof \JsonSerializable) {
                    $array[$key] = $value->jsonSerialize();
                } else {
                    $array[$key] = self::toArray($value);
                }
            } else {
                if (isset($key)) {
                    $array[$key] = $value;
                }
            }
        }

        return $array;
    }
}
