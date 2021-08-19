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

use DI\Definition\Helper\DefinitionHelper;

/**
 * Class Container.
 *
 * @method static mixed get(string $name)
 * @method static mixed has(string $name)
 * @method static mixed set(string $name, mixed|DefinitionHelper $value)
 * @method static mixed make(string $name, array $parameters = [])
 * @method static object injectOn($instance)
 * @method static mixed call(callable $callable, array $parameters = [])
 * @method static string[] getKnownEntryNames()
 * @method static string debugEntry(string $name)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Container extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor(): string
    {
        return \DI\Container::class;
    }
}
