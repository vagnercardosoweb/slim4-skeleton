<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class TrailingSlashMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class TrailingSlashMiddleware implements MiddlewareInterface
{
    /**
     * TrailingSlashMiddleware constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ('/' != $path && str_ends_with($path, '/')) {
            while (str_ends_with($path, '/')) {
                $path = substr($path, 0, -1);
            }

            $uri = $uri->withPath($path);

            if ('GET' == $request->getMethod()) {
                return $this->container->get(ResponseInterface::class)
                    ->withStatus(StatusCodeInterface::STATUS_MOVED_PERMANENTLY)
                    ->withHeader('Location', (string)$uri)
                ;
            }

            $request = $request->withUri($uri);
        }

        return $handler->handle($request);
    }
}
