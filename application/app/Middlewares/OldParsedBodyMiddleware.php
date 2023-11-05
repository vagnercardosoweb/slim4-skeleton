<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Twig\Twig;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class OldParsedBodyMiddleware implements MiddlewareInterface
{
    public function __construct(private ContainerInterface $container) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->container->has(Twig::class) && 'GET' !== strtoupper($request->getMethod())) {
            /** @var Twig $twig */
            $twig = $this->container->get(Twig::class);
            $twig->addGlobal('oldParsedBody', $request->getParsedBody());
        }

        return $handler->handle($request);
    }
}
