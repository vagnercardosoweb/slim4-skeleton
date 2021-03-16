<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 16/03/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Core\Curl\Curl as CoreCurl;

/**
 * Class Curl.
 *
 * @method static \Core\Curl\Response get(string $endPoint, $params = [])
 * @method static \Core\Curl\Response post(string $endPoint, $params = [])
 * @method static \Core\Curl\Response put(string $endPoint, $params = [])
 * @method static \Core\Curl\Response delete(string $endPoint, $params = [])
 * @method static \Core\Curl\Response create(string $method, string $endPoint, $params = null)
 * @method static array getHeaders()
 * @method static CoreCurl setHeaders(array | string $keys, $value = null)
 * @method static array getOptions()
 * @method static CoreCurl setOptions(array | string $options, $value = null)
 * @method static CoreCurl clear()
 */
class Curl extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return CoreCurl::class;
    }
}
