<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace Tests\Traits;

use Slim\Interfaces\RouteParserInterface;

trait RouteTestTrait
{
    /**
     * Build the path for a named route including the base path.
     *
     * @param string   $routeName   Route name
     * @param string[] $data        Named argument replacement data
     * @param string[] $queryParams Optional query string parameters
     *
     * @return string route with base path
     */
    protected function getUrlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->container->get(RouteParserInterface::class)->urlFor($routeName, $data, $queryParams);
    }
}
