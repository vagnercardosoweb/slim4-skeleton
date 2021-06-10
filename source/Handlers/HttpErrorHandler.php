<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 10/06/2021 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core\Handlers;

use Core\Exception\HttpUnavailableException;
use Core\Handlers\ErrorHandler as MyErrorHandler;
use Core\Support\Env;
use Core\Support\Path;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Response;

class HttpErrorHandler
{
    /**
     * @var string[]
     */
    protected array $types = [
        HttpNotFoundException::class => MyErrorHandler::RESOURCE_NOT_FOUND,
        HttpMethodNotAllowedException::class => MyErrorHandler::NOT_ALLOWED,
        HttpUnauthorizedException::class => MyErrorHandler::UNAUTHENTICATED,
        HttpForbiddenException::class => MyErrorHandler::UNAUTHENTICATED,
        HttpBadRequestException::class => MyErrorHandler::BAD_REQUEST,
        HttpNotImplementedException::class => MyErrorHandler::NOT_IMPLEMENTED,
        HttpUnavailableException::class => MyErrorHandler::SERVICE_UNAVAILABLE,
        HttpInternalServerErrorException::class => MyErrorHandler::INTERNAL_SERVER_ERROR,
    ];

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $exception
     * @param bool                                     $displayErrorDetails
     * @param bool                                     $logErrors
     * @param bool                                     $logErrorDetails
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $type = $this->types[$exception::class] ?? MyErrorHandler::BAD_REQUEST;
        $statusCode = $exception->getCode() ?: StatusCodeInterface::STATUS_BAD_REQUEST;
        $message = $exception->getMessage();

        if (
            !is_integer($statusCode)
            || $statusCode < StatusCodeInterface::STATUS_CONTINUE
            || $statusCode > 599
        ) {
            $type = MyErrorHandler::SERVER_ERROR;
            $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
        }

        $error = [
            'statusCode' => $statusCode,
            'error' => [
                'type' => $type,
                'name' => basename(str_replace('\\', '/', get_class($exception))),
                'message' => $message,
                'typeClass' => MyErrorHandler::getHtmlClass($statusCode),
            ],
        ];

        if (($displayErrorDetails && 'development' === Env::get('APP_ENV')) || isset($_GET['showAllErrors'])) {
            $error['error'] += [
                'line' => $exception->getLine(),
                'file' => str_replace(Path::app(), '', $exception->getFile()),
                'route' => "({$request->getMethod()}) {$request->getUri()->getPath()}",
                'trace' => explode("\n", $exception->getTraceAsString()),
                'headers' => array_map(fn ($header) => $header[0], $request->getHeaders()),
                'queryParams' => $request->getQueryParams(),
                'parsedBody' => $request->getParsedBody(),
                'cookieParams' => $request->getCookieParams(),
            ];
        }

        $payload = json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $response = new Response($statusCode);
        $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($payload);

        return $response;
    }
}
