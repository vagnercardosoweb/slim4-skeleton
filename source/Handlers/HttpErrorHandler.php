<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2023 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core\Handlers;

use Core\Exception\HttpUnavailableException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;

class HttpErrorHandler extends ErrorHandler
{
    protected array $types = [
        HttpNotFoundException::class => ErrorHandler::RESOURCE_NOT_FOUND,
        HttpMethodNotAllowedException::class => ErrorHandler::NOT_ALLOWED,
        HttpUnauthorizedException::class => ErrorHandler::UNAUTHENTICATED,
        HttpForbiddenException::class => ErrorHandler::UNAUTHENTICATED,
        HttpBadRequestException::class => ErrorHandler::BAD_REQUEST,
        HttpNotImplementedException::class => ErrorHandler::NOT_IMPLEMENTED,
        HttpUnavailableException::class => ErrorHandler::SERVICE_UNAVAILABLE,
        HttpInternalServerErrorException::class => ErrorHandler::INTERNAL_SERVER_ERROR,
    ];

    public function respond(): ResponseInterface
    {
        $type = $this->types[$this->exception::class] ?? ErrorHandler::BAD_REQUEST;
        $statusCode = $this->exception->getCode() ?: StatusCodeInterface::STATUS_BAD_REQUEST;
        $validStatusCodes = (new \ReflectionClass(StatusCodeInterface::class))->getConstants();

        if (!in_array($statusCode, $validStatusCodes)) {
            $type = ErrorHandler::SERVER_ERROR;
            $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
        }

        $error = [
            'name' => basename(str_replace('\\', '/', get_class($this->exception))),
            'code' => $type,
            'statusCode' => $statusCode,
            'errorId' => mb_strtoupper(bin2hex(random_bytes(8))),
            'message' => $this->exception->getMessage(),
            'colorName' => ErrorHandler::toHtmlClass($statusCode),
        ];

        if ($this->displayErrorDetails) {
            $this->logger->error('error', [
                'path' => $this->request->getUri()->getPath(),
                'method' => $this->request->getMethod(),
                'context' => [],
                'body' => $this->request->getParsedBody(),
                'headers' => array_map(fn ($header) => $header[0], $this->request->getHeaders()),
                'query' => $this->request->getQueryParams(),
                'cookies' => $this->request->getCookieParams(),
                'error' => [
                    ...$error,
                    'line' => $this->exception->getLine(),
                    'file' => $this->exception->getFile(),
                    'stack' => explode("\n", $this->exception->getTraceAsString()),
                ],
            ]);
        }

        $response = $this->responseFactory->createResponse($statusCode)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));

        return $response;
    }

    protected function logError(string $error): void
    {
    }
}
