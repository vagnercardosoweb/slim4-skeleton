<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace Core\Facades;

use Psr\Container\ContainerInterface;

class Container extends Facade
{
    protected static function getAccessor(): string
    {
        return ContainerInterface::class;
    }
}
