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

namespace Core\Handlers;

use Core\Exception\HttpUnavailableException;
use Core\Exception\HttpUnprocessableEntityException;
use Core\Support\Str;
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
    protected array $types = [
        HttpNotFoundException::class => 'RESOURCE_NOT_FOUND',
        HttpMethodNotAllowedException::class => 'NOT_ALLOWED',
        HttpUnauthorizedException::class => 'UNAUTHENTICATED',
        HttpNotImplementedException::class => 'NOT_IMPLEMENTED',
        HttpInternalServerErrorException::class => 'INTERNAL_SERVER_ERROR',
        HttpUnprocessableEntityException::class => 'UNPROCESSABLE_ENTITY',
        HttpUnavailableException::class => 'SERVICE_UNAVAILABLE',
        HttpBadRequestException::class => 'BAD_REQUEST',
        HttpForbiddenException::class => 'UNAUTHENTICATED',
    ];

    public function respond(): ResponseInterface
    {
        $type = $this->types[$this->exception::class] ?? 'BAD_REQUEST';
        $statusCode = $this->exception->getCode() ?: StatusCodeInterface::STATUS_BAD_REQUEST;
        $validStatusCodes = (new \ReflectionClass(StatusCodeInterface::class))->getConstants();

        $errorId = Str::randomHexBytes(22);
        $message = $this->exception->getMessage();

        if ($this->exception::class === \ErrorException::class || !in_array($statusCode, $validStatusCodes)) {
            $type = 'INTERNAL_SERVER_ERROR';
            $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
            $message = 'Internal server error, report the code [{{errorId}}] to support.';
        }

        $errorMessage = str_replace('{{errorId}}', $errorId, $message);
        $requestId = $this->request->getAttribute('requestId') ?? Str::uuid();

        $error = [
            'name' => basename(str_replace('\\', '/', get_class($this->exception))),
            'code' => $type,
            'statusCode' => $statusCode,
            'errorId' => $errorId,
            'requestId' => $requestId,
            'message' => $errorMessage,
        ];

        if ($this->logErrorDetails) {
            $this->logger->error('HTTP_REQUEST_ERROR', [
                'path' => $this->request->getUri()->getPath(),
                'method' => $this->request->getMethod(),
                // 'context' => [],
                'body' => $this->request->getParsedBody(),
                // 'headers' => array_map(fn ($header) => $header[0], $this->request->getHeaders()),
                'query' => $this->request->getQueryParams(),
                // 'cookies' => $this->request->getCookieParams(),
                'error' => [
                    ...$error,
                    'message' => $this->exception->getMessage(),
                    'line' => $this->exception->getLine(),
                    'file' => $this->exception->getFile(),
                    // 'stack' => explode("\n", $this->exception->getTraceAsString()),
                ],
            ]);
        }

        $response = $this->responseFactory
            ->createResponse($statusCode)
            ->withHeader('X-Request-ID', $requestId)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ;

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $allowedMethods = implode(', ', $this->exception->getAllowedMethods());
            $response = $response->withHeader('Allow', $allowedMethods);
        }

        $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));

        return $response;
    }

    protected function writeToErrorLog(): void {}
}
