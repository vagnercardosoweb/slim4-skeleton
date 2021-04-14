<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Core\Support;

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
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4))) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getIpAddress(): string
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $realIp = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $realIp = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $realIp = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $realIp = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $realIp = getenv('HTTP_FORWARDED');
        } elseif (getenv('REMOTE_ADDR')) {
            $realIp = getenv('REMOTE_ADDR');
        } else {
            $realIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
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

        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('|MSIE ([0-9].[0-9]{1,2})|', $userAgent, $matched)) {
            $browser = 'IE';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Opera/([0-9].[0-9]{1,2})|', $userAgent, $matched)) {
            $browser = 'Opera';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Firefox/([0-9\.]+)|', $userAgent, $matched)) {
            $browser = 'Firefox';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Chrome/([0-9\.]+)|', $userAgent, $matched)) {
            $browser = 'Chrome';
            $browserVersion = $matched[1];
        } elseif (preg_match('|Safari/([0-9\.]+)|', $userAgent, $matched)) {
            $browser = 'Safari';
            $browserVersion = $matched[1];
        } else {
            $browser = 'Outro';
            $browserVersion = 0;
        }

        if (preg_match('|Mac|', $userAgent, $matched)) {
            $so = 'MAC';
        } elseif (preg_match('|Windows|', $userAgent, $matched) || preg_match('|WinNT|', $userAgent, $matched) || preg_match('|Win95|', $userAgent, $matched)) {
            $so = 'Windows';
        } elseif (preg_match('|Linux|', $userAgent, $matched)) {
            $so = 'Linux';
        } else {
            $so = 'Outro';
        }

        return [
            'so' => $so,
            'browser' => $browser,
            'version' => $browserVersion,
            'user_agent' => $userAgent,
        ];
    }

    /**
     * @param array       $array
     * @param string|null $prefix
     *
     * @return string
     */
    public static function httpBuildQuery(array $array, ?string $prefix = null): string
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
                $build[] = $key.'='.urlencode($value);
            }
        }

        return implode('&', $build);
    }

    /**
     * @param mixed        $encoded
     * @param array|string $result
     */
    public static function parseStr(mixed $encoded, array | string &$result)
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
        $bytes = $bytes / pow(1000, $base);

        return number_format(
                round($bytes, $precision),
                2,
                ',',
                ''
            ).' '.$units[$base];
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
     * @param $value
     *
     * @return mixed
     */
    public static function normalizeValue($value): mixed
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

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value
        };
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
    public static function filterRequestValues(array | string $values): array
    {
        $result = [];

        if (!is_array($values)) {
            $values = [$values];
        }

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::filterRequestValues($value);
            } else {
                $result[$key] = addslashes(strip_tags(trim(filter_var($value, FILTER_DEFAULT))));
            }
        }

        return $result;
    }

    /**
     * @param string|int $value
     *
     * @return string|null
     */
    public static function onlyNumber(int | string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return trim(preg_replace('/[^0-9]/', '', $value));
    }

    /**
     * @param string $xml
     * @param string $className
     * @param int    $option
     * @param string $ns
     * @param bool   $isPrefix
     *
     * @return \SimpleXMLElement|null
     */
    public static function parseXml(
        string $xml,
        string $className = 'SimpleXMLElement',
        int $option = 0,
        string $ns = '',
        bool $isPrefix = false
    ): ?\SimpleXMLElement {
        if (empty($xml)) {
            return null;
        }

        if (false !== stripos($xml, '<!DOCTYPE html>')) {
            return null;
        }

        libxml_use_internal_errors(true);

        $xml = trim($xml);
        $xml = simplexml_load_string($xml, $className, $option, $ns, $isPrefix);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        if (!empty($errors)) {
            return null;
        }

        return $xml;
    }

    /**
     * @param mixed $json
     * @param bool  $assoc
     * @param int   $depth
     * @param int   $options
     *
     * @return array|object|null
     */
    public static function parseJson(
        mixed $json,
        bool $assoc = false,
        int $depth = 512,
        int $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    ): object | array | null {
        if (!is_string($json)) {
            $json = json_encode($json);
        }

        $json = json_decode($json, $assoc, $depth, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        return $json;
    }

    /**
     * @param mixed $value
     *
     * @return int|string
     */
    public static function serialize(mixed $value): int | string
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
        } catch (\Exception) {
            return $value;
        }
    }

    /**
     * @param array|object $data
     *
     * @return bool
     */
    public static function emptyArrayRecursive(object | array $data): bool
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
