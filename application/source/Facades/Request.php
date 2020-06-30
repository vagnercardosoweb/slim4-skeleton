<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 30/06/2020 Vagner Cardoso
 */

namespace Core\Facades;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Request.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Request extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return ServerRequestInterface::class;
    }
}
