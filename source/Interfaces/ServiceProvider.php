<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 28/02/2023 Vagner Cardoso
 */

namespace Core\Interfaces;

use DI\Container;

/**
 * Interface ServiceProvider.
 */
interface ServiceProvider
{
    /**
     * @param \DI\Container $container
     *
     * @return mixed
     */
    public function __invoke(Container $container): mixed;
}
