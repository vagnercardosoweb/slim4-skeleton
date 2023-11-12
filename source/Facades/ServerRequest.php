<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

namespace Core\Facades;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Facade
{
    protected static function getAccessor(): string
    {
        return ServerRequestInterface::class;
    }
}
