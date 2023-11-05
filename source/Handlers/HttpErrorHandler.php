<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/11/2023 Vagner Cardoso
 */

declare(strict_types=1);

namespace Core\Handlers;

use Core\Exception\HttpUnavailableException;
use Core\Support\Str;
use ErrorException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
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
        HttpForbiddenException::class => 'UNAUTHENTICATED',
        HttpBadRequestException::class => 'BAD_REQUEST',
        HttpNotImplementedException::class => 'NOT_IMPLEMENTED',
        HttpUnavailableException::class => 'SERVICE_UNAVAILABLE',
        HttpInternalServerErrorException::class => 'INTERNAL_SERVER_ERROR',
    ];

    public function respond(): ResponseInterface
    {
        $type = $this->types[$this->exception::class] ?? 'BAD_REQUEST';
        $statusCode = $this->exception->getCode() ?: StatusCodeInterface::STATUS_BAD_REQUEST;
        $validStatusCodes = (new ReflectionClass(StatusCodeInterface::class))->getConstants();

        $errorId = mb_strtoupper(Str::randomHexBytes());
        $message = $this->exception->getMessage();

        if ($this->exception::class === ErrorException::class || !in_array($statusCode, $validStatusCodes)) {
            $type = 'INTERNAL_SERVER_ERROR';
            $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
            $message = 'Internal server error, report the code [{{errorId}}] to support.';
        }

        $errorMessage = str_replace('{{errorId}}', $errorId, $message);
        $error = [
            'name' => basename(str_replace('\\', '/', get_class($this->exception))),
            'code' => $type,
            'statusCode' => $statusCode,
            'errorId' => $errorId,
            'message' => $errorMessage,
            'color' => HttpErrorHandler::colorFromStatusCode($statusCode),
        ];

        if ($this->logErrorDetails) {
            $this->logger->error('error', [
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
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $allowedMethods = implode(', ', $this->exception->getAllowedMethods());
            $response = $response->withHeader('Allow', $allowedMethods);
        }

        $response->getBody()->write(json_encode($error, JSON_PRETTY_PRINT));

        return $response;
    }

    public static function colorFromStatusCode(int|string $code): string
    {
        if (is_string($code) && 200 != $code) {
            $code = E_USER_ERROR;
        }

        return match ($code) {
            E_USER_NOTICE, E_NOTICE => 'info',
            E_USER_WARNING, E_WARNING => 'warning',
            200 => 'success',
            default => 'danger',
        };
    }

    protected function writeToErrorLog(): void {}
}
