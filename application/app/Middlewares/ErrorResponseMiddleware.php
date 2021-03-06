<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 09/07/2021 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Facades\Container;
use Core\Support\Env;
use Core\Twig\Twig;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Class ErrorResponseMiddleware.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ErrorResponseMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Exception $exception) {
            if (Container::has(Twig::class) && !Env::get('APP_RESPONSE_ERROR_JSON', false)) {
                $statusCode = StatusCodeInterface::STATUS_BAD_REQUEST;
                $validStatusCodes = (new \ReflectionClass(StatusCodeInterface::class))->getConstants();

                if (in_array($exception->getCode(), $validStatusCodes)) {
                    $statusCode = $exception->getCode();
                }

                /** @var Twig $twig */
                $twig = Container::get(Twig::class);
                $template = 'index';

                if ($twig->exists("@errors.{$statusCode}")) {
                    $template = "{$statusCode}.twig";
                }

                return $twig
                    ->render(
                        new Response(),
                        "@errors.{$template}",
                        ['exception' => $exception],
                        $statusCode
                    )
                ;
            }

            throw $exception;
        }
    }
}
