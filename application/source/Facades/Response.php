<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 31/01/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Psr\Http\Message\ResponseInterface;

/**
 * Class Response.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Response extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return ResponseInterface::class;
    }
}
