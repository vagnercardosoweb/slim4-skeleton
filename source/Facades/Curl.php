<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 24/02/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Core\Curl\Curl as CoreCurl;

/**
 * Class Curl.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
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
