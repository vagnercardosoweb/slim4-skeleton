<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @param ContainerInterface $container
     */
    public function __construct(private ContainerInterface $container) {}

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return ResponseInterface
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
