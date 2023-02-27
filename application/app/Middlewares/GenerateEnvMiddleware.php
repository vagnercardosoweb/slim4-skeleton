<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 27/02/2023 Vagner Cardoso
 */

namespace App\Middlewares;

use Core\Support\Env;
use Core\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GenerateEnvMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach (['APP_KEY', 'API_SECRET_KEY', 'DEPLOY_SECRET_KEY'] as $key) {
            $value = Env::get($key);

            if (empty($value)) {
                $quote = preg_quote("={$value}", '/');
                $random = Str::randomHexBytes(60);
                $pathEnv = Env::path();

                file_put_contents(
                    $pathEnv,
                    preg_replace(
                        "/^{$key}{$quote}.*/m",
                        "{$key}=vcw:{$random}",
                        file_get_contents($pathEnv)
                    )
                );
            }
        }

        return $handler->handle($request);
    }
}
