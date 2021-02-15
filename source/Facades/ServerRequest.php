<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 15/02/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ServerRequest.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ServerRequest extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return ServerRequestInterface::class;
    }
}
