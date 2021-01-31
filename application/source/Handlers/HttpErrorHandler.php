<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 31/01/2021 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core\Handlers;

use Core\Exception\HttpUnavailableException;
use Core\Handlers\ErrorHandler as MyErrorHandler;
use Core\Helpers\Path;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;

class HttpErrorHandler extends ErrorHandler
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $type = $this->types[$exception::class] ?? MyErrorHandler::BAD_REQUEST;
        $statusCode = $exception->getCode();
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

        if ($this->displayErrorDetails) {
            $error['error'] += [
                'line' => $exception->getLine(),
                'file' => str_replace(Path::app(), '', $exception->getFile()),
                'route' => "({$this->method}) {$this->request->getUri()->getPath()}",
                'trace' => explode("\n", $exception->getTraceAsString()),
                'headers' => array_map(fn ($header) => $header[0], $this->request->getHeaders()),
                'queryParams' => $this->request->getQueryParams(),
                'parsedBody' => $this->request->getParsedBody(),
                'cookieParams' => $this->request->getCookieParams(),
            ];
        }

        $payload = json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($payload);

        return $response;
    }
}
