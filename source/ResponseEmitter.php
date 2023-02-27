<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

declare(strict_types = 1);

namespace Core;

use Core\Support\Env;
use Psr\Http\Message\ResponseInterface;
use Slim\ResponseEmitter as SlimResponseEmitter;

class ResponseEmitter extends SlimResponseEmitter
{
    public function emit(ResponseInterface $response): void
    {
        $origin = Env::get('CORS_ORIGIN', '*');
        $methods = Env::get('CORS_METHODS', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $headers = Env::get('CORS_HEADERS', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');

        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Headers', $headers)
            ->withHeader('Access-Control-Allow-Methods', $methods)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
        ;

        if (ob_get_contents()) {
            ob_clean();
        }

        parent::emit($response);
    }
}
