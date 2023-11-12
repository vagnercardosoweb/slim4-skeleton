<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 06/11/2023 Vagner Cardoso
 */

declare(strict_types=1);

namespace Core\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\ResponseEmitter;

readonly class ShutdownErrorHandler
{
    public function __construct(
        private ServerRequestInterface $request,
        private HttpErrorHandler       $errorHandler,
    )
    {
    }

    public function __invoke(): void
    {
        if (!error_get_last()) {
            return;
        }

        $message = 'An error while processing your request. Please try again later.';
        $exception = new HttpInternalServerErrorException($this->request, $message);
        $response = $this->errorHandler->__invoke($this->request, $exception, true, true, true);

        if (ob_get_length()) {
            ob_clean();
        }

        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
        exit;
    }
}
