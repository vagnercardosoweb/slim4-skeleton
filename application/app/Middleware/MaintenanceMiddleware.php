<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 31/01/2021 Vagner Cardoso
 */

namespace App\Middleware;

use Core\Exception\HttpUnavailableException;
use Core\Helpers\Env;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class MaintenanceMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class MaintenanceMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @throws \Core\Exception\HttpUnavailableException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Env::get('APP_MAINTENANCE', false)) {
            throw new HttpUnavailableException($request);
        }

        return $handler->handle($request);
    }
}
