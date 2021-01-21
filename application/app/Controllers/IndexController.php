<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 21/01/2021 Vagner Cardoso
 */

namespace App\Controllers;

use Core\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class IndexController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IndexController extends BaseController
{
    /**
     * @param                                     $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->withJson($response, [
            'version' => App::VERSION,
            'datetime' => new \DateTimeImmutable(),
        ]);
    }
}
