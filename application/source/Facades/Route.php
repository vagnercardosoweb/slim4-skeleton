<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/01/2021 Vagner Cardoso
 */

namespace Core\Facades;

use Slim\Interfaces\RouteInterface;

/**
 * Class Route.
 *
 * @method static RouteInterface get(string $pattern, $callable)
 * @method static RouteInterface post(string $pattern, $callable)
 * @method static RouteInterface put(string $pattern, $callable)
 * @method static RouteInterface patch(string $pattern, $callable)
 * @method static RouteInterface delete(string $pattern, $callable)
 * @method static RouteInterface options(string $pattern, $callable)
 * @method static RouteInterface any(string $pattern, $callable)
 * @method static RouteInterface map(array $methods, string $pattern, $callable)
 * @method static RouteInterface group(string $pattern, $callable)
 * @method static RouteInterface redirect(string $from, $to, int $status = 302)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Route extends App
{
}
