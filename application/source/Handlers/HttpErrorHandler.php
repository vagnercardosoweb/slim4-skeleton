<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 02/07/2020 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core\Handlers;

use Core\Helpers\Path;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;

class HttpErrorHandler extends ErrorHandler
{
    public const BAD_REQUEST = 'BAD_REQUEST';

    public const NOT_ALLOWED = 'NOT_ALLOWED';

    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';

    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';

    public const SERVER_ERROR = 'SERVER_ERROR';

    public const UNAUTHENTICATED = 'UNAUTHENTICATED';

    /**
     * @param int|string $code
     *
     * @return string
     */
    public static function getErrorType($code): string
    {
        if (is_string($code) && 200 !== $code) {
            $code = E_USER_ERROR;
        }

        switch ($code) {
            case E_USER_NOTICE:
            case E_NOTICE:
                $result = 'info';
                break;

            case E_USER_WARNING:
            case E_WARNING:
                $result = 'warning';
                break;

            case E_USER_ERROR:
            case E_ERROR:
            case '0':
                $result = 'danger';
                break;

            case 200:
                $result = 'success';
                break;

            default:
                $result = 'danger';
        }

        return $result;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function respond(): ResponseInterface
    {
        $type = self::BAD_REQUEST;
        $exception = $this->exception;
        $statusCode = $exception->getCode();
        $message = $exception->getMessage();

        if (!is_integer($statusCode) || $statusCode < StatusCodeInterface::STATUS_CONTINUE || $statusCode > 599) {
            $type = self::SERVER_ERROR;
            $statusCode = 500;
        }

        if ($exception instanceof HttpNotFoundException) {
            $type = self::RESOURCE_NOT_FOUND;
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $type = self::NOT_ALLOWED;
        } elseif ($exception instanceof HttpUnauthorizedException) {
            $type = self::UNAUTHENTICATED;
        } elseif ($exception instanceof HttpForbiddenException) {
            $type = self::UNAUTHENTICATED;
        } elseif ($exception instanceof HttpBadRequestException) {
            $type = self::BAD_REQUEST;
        } elseif ($exception instanceof HttpNotImplementedException) {
            $type = self::NOT_IMPLEMENTED;
        }

        $error = [
            'statusCode' => $statusCode,
            'error' => [
                'type' => $type,
                'name' => basename(str_replace('\\', '/', get_class($exception))),
                'message' => $message,
                'typeClass' => self::getErrorType($statusCode),
            ],
        ];

        if ($this->displayErrorDetails) {
            $error['error'] = $error['error'] + [
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

        $payload = json_encode($error, JSON_PRETTY_PRINT);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
