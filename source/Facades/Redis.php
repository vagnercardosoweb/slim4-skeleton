<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/08/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Predis\Client;

/**
 * Class Redis.
 */
class Redis extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return Client::class;
    }
}
