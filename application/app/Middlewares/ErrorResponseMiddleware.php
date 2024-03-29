<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 12/11/2023 Vagner Cardoso
 */

declare(strict_types = 1);

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/02/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Facades\Container;
use Core\Support\Env;
use Core\Support\Str;
use Core\Twig\Twig;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorResponseMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Exception $exception) {
            if (
                Env::get('APP_ONLY_API', false)
                || str_starts_with($request->getUri()->getPath(), '/api/')
                || 'XMLHttpRequest' === $request->getHeaderLine('X-Requested-With')
                || !Container::has(Twig::class)
            ) {
                throw $exception;
            }

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

            $requestId = $request->getAttribute('requestId') ?? Str::uuid();
            $response = Container::get(ResponseInterface::class)
                ->withStatus($statusCode)
                ->withHeader('X-Request-Id', $requestId)
            ;

            return $twig
                ->render(
                    $response,
                    "@errors.{$template}",
                    ['exception' => $exception],
                    $statusCode
                )
            ;
        }
    }
}
