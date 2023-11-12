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

use Exception;
use JsonException;
use SimpleXMLElement;

/**
 * Class Helper.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Common
{
    /**
     * @return bool
     */
    public static function isMobile(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/Android|iPhone|Opera Mini|Mobile|Windows Phone/i', $userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getIpAddress(): string
    {
        $varNames = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        $realIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        foreach ($varNames as $varName) {
            $varValue = getenv($varName);

            if (!empty($varValue)) {
                $realIp = $varValue;
                break;
            }
        }

        if (false !== mb_strpos($realIp, ',')) {
            $ip = explode(',', $realIp);

            $realIp = $ip[0];
        }

        return $realIp;
    }

    /**
     * @return array
     */
    public static function getUserAgent(): array
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return [
                'so' => PHP_OS_FAMILY,
                'browser' => 'unknown',
                'version' => '0.0.0',
                'user_agent' => PHP_SAPI,
            ];
        }

        $so = 'unknown';
        $browser = 'unknown';
        $browserVersion = 0;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('|MSIE ([0-9].[0-9]{1,2})|', $userAgent, $matched)) {
            $browser = 'IE';
            $browserVersion = $matched[1];
        }

        if (preg_match('|Opera/([0-9].[0-9]{1,2})|', $userAgent, $matched)) {
            $browser = 'Opera';
            $browserVersion = $matched[1];
        }

        if (preg_match('|Firefox/([0-9\.]+)|', $userAgent, $matched)) {
            $browser = 'Firefox';
            $browserVersion = $matched[1];
        }

        if (preg_match('|Chrome/([0-9\.]+)|', $userAgent, $matched)) {
            $browser = 'Chrome';
            $browserVersion = $matched[1];
        }

        if (preg_match('|Safari/([0-9\.]+)|', $userAgent, $matched)) {
            $browser = 'Safari';
            $browserVersion = $matched[1];
        }

        if (preg_match('|Mac|', $userAgent, $matched)) {
            $so = 'MAC';
        }

        if (preg_match('|Windows|', $userAgent, $matched) || preg_match('|WinNT|', $userAgent, $matched) || preg_match('|Win95|', $userAgent, $matched)) {
            $so = 'Windows';
        }

        if (preg_match('|Linux|', $userAgent, $matched)) {
            $so = 'Linux';
        }

        return [
            'so' => $so,
            'browser' => $browser,
            'version' => $browserVersion,
            'user_agent' => $userAgent,
        ];
    }

    /**
     * @param array $array
     * @param string|null $prefix
     *
     * @return string
     */
    public static function httpBuildQuery(array $array, string $prefix = null): string
    {
        $build = [];

        foreach ($array as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            if ($prefix && $key && !is_int($key)) {
                $key = "{$prefix}[{$key}]";
            } elseif ($prefix) {
                $key = "{$prefix}[]";
            }
            if (is_array($value)) {
                $build[] = self::httpBuildQuery($value, $key);
            } else {
                $build[] = sprintf('%s=%s', $key, urlencode($value));
            }
        }

        return implode('&', $build);
    }

    public static function redactKeys(array $values): array
    {
        $sensitiveKeys = explode(',', Env::get('REDACTED_KEYS', ''));

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = self::redactKeys($value);
            }

            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $values[$key] = '[Redacted]';
            }
        }

        return $values;
    }

    /**
     * @param mixed $encoded
     * @param array|string $result
     */
    public static function parseStr(mixed $encoded, array|string &$result): void
    {
        if (empty($encoded)) {
            return;
        }

        if (is_string($encoded)) {
            mb_parse_str($encoded, $encoded);
        }

        if (is_string($result)) {
            mb_parse_str($result, $result);
        }

        foreach ($encoded as $key => $value) {
            $result[$key] = $value;
        }
    }

    /**
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public static function convertBytesForHuman(int $bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max($bytes, 0);
        $base = floor(log($bytes) / log(1024));
        $base = min($base, count($units) - 1);
        $bytes /= pow(1000, $base);

        return number_format(
                round($bytes, $precision),
                2,
                ',',
                ''
            ) . ' ' . $units[$base];
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function normalizeValue(mixed $value): mixed
    {
        if (is_array($value) || is_object($value)) {
            return $value;
        }

        if (is_integer($value)) {
            return (int)$value;
        }

        if (is_float($value)) {
            return self::normalizeFloat($value);
        }

        return match (strtolower((string)$value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value
        };
    }

    /**
     * @param $value
     *
     * @return float
     */
    public static function normalizeFloat($value): float
    {
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return (float)$value;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public static function normalizeDateFormat(string $format): string
    {
        return str_replace('/', '-', $format);
    }

    /**
     * @param array|string $values
     *
     * @return array
     */
    public static function filterValues(array|string $values): array
    {
        $result = [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::filterValues($value);
            } else {
                $result[$key] = addslashes(strip_tags(trim(filter_var($value))));
            }
        }

        return $result;
    }

    /**
     * @param string|int $value
     *
     * @return string|null
     */
    public static function onlyNumber(int|string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return trim(preg_replace('/[^0-9]/', '', $value));
    }

    /**
     * @param string $xml
     * @param string $className
     * @param int $option
     * @param string $ns
     * @param bool $isPrefix
     *
     * @return SimpleXMLElement|null
     */
    public static function parseXml(
        string $xml,
        string $className = 'SimpleXMLElement',
        int    $option = 0,
        string $ns = '',
        bool   $isPrefix = false
    ): ?SimpleXMLElement
    {
        if (empty($xml)) {
            return null;
        }

        if (false !== stripos($xml, '<!DOCTYPE html>')) {
            return null;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string(trim($xml), $className, $option, $ns, $isPrefix);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (!empty($errors)) {
            return null;
        }

        return $xml;
    }

    /**
     * @param string $value
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     *
     * @return object|array
     * @throws JsonException
     */
    public static function parseJson(
        string $value,
        bool   $assoc = false,
        int    $depth = 512,
        int    $options = 0
    ): array|object
    {
        $value = json_decode($value, $assoc, $depth, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException(sprintf('Invalid JSON decode: %s', json_last_error_msg()));
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return int|string
     */
    public static function serialize(mixed $value): int|string
    {
        if (
            is_numeric($value)
            && !in_array($value, [INF, -INF])
            && !is_nan($value)
        ) {
            return $value;
        }

        return serialize($value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function unserialize(mixed $value): mixed
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        try {
            return unserialize($value);
        } catch (Exception) {
            return $value;
        }
    }

    /**
     * @param array|object $data
     *
     * @return bool
     */
    public static function emptyArrayRecursive(array|object $data): bool
    {
        if (empty($data)) {
            return true;
        }

        $data = Obj::toArray($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                return self::emptyArrayRecursive($value);
            }

            if (empty($value) && '0' != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public static function generateHexColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    public static function getYoutubeVideoId(string $url): ?string
    {
        if (strpos($url, 'youtu.be/')) {
            preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $matches);

            return $matches[4];
        }

        if (strstr($url, '/v/')) {
            $aux = explode('v/', $url);
            $aux2 = explode('?', $aux[1]);

            return $aux2[0];
        }

        if (strstr($url, 'v=')) {
            $aux = explode('v=', $url);
            $aux2 = explode('&', $aux[1]);

            return $aux2[0];
        }

        if (strstr($url, '/embed/')) {
            $aux = explode('/embed/', $url);

            return $aux[1];
        }

        if (strstr($url, 'be/')) {
            $aux = explode('be/', $url);

            return $aux[1];
        }

        return null;
    }
}
